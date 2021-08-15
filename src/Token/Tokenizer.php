<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use LogicException;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Source;

/**
 * An override of Twig's Lexer to add whitespace and new line detection.
 */
final class Tokenizer implements TokenizerInterface
{
    private const STATE_DATA          = 0;
    private const STATE_BLOCK         = 1;
    private const STATE_VAR           = 2;
    private const STATE_DQ_STRING     = 3;
    private const STATE_INTERPOLATION = 4;
    private const STATE_COMMENT       = 5;

    private const SQ_STRING_PART = '[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*';
    private const DQ_STRING_PART = '[^#"\\\\]*(?:(?:\\\\.|#(?!\{))[^#"\\\\]*)*';

    private const REGEX_EXPRESSION_START    = '/({%|{#|{{)(-|~)?/';
    private const REGEX_BLOCK_END           = '/(?:-|~)?(?:%})/A';
    private const REGEX_COMMENT_END         = '/(?:-|~)?(?:#})/'; // Must not be anchored
    private const REGEX_VAR_END             = '/(?:-|~)?(?:}})/A';
    private const REGEX_INTERPOLATION_START = '/#{/A';
    private const REGEX_INTERPOLATION_END   = '/}/A';

    private const REGEX_NAME            = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/A';
    private const REGEX_NUMBER          = '/[0-9]+(?:\.[0-9]+)?/A';
    private const REGEX_STRING          = '/"('.self::DQ_STRING_PART.')"|\'('.self::SQ_STRING_PART.')\'/As';
    private const REGEX_DQ_STRING_PART  = '/'.self::DQ_STRING_PART.'/As';
    private const REGEX_DQ_STRING_DELIM = '/"/A';

    /**
     * @var string
     */
    private $operatorRegex;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * @var int|null
     */
    private $end;

    /**
     * @var int
     */
    private $line = 1;

    /**
     * @var int
     */
    private $currentPosition = 0;

    /**
     * @var array<int, Token>
     */
    private $tokens = [];

    /**
     * @var array<int, array{fullMatch: string, position: int, match: string}>
     */
    private $tokenPositions = [];

    /**
     * @var array<array{int, array<string, string>}>
     *
     * @psalm-var array<array{0|1|2|3|4|5, array<string, string>}>
     */
    private $state = [];

    /**
     * @var array<int, Token>
     */
    private $bracketsAndTernary = [];

    /**
     * @var string
     */
    private $code = '';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @param Environment $env
     *
     * @return void
     */
    public function __construct(Environment $env)
    {
        // Caching the regex.
        $this->operatorRegex = $this->getOperatorRegex($env);
    }

    /**
     * @param Source $source
     *
     * @return array<int, Token>
     *
     * @throws SyntaxError
     */
    public function tokenize(Source $source): array
    {
        $this->resetState($source);
        $this->preflightSource($this->code);

        while ($this->cursor < $this->end) {
            $lastToken = $this->getTokenPosition();
            $nextToken = $this->getTokenPosition(1);

            while (null !== $nextToken && $nextToken['position'] < $this->cursor) {
                $this->moveCurrentPosition();
                $lastToken = $nextToken;
                $nextToken = $this->getTokenPosition(1);
            }

            switch ($this->getState()) {
                case self::STATE_BLOCK:
                    $this->lexBlock();
                    break;
                case self::STATE_VAR:
                    $this->lexVariable();
                    break;
                case self::STATE_COMMENT:
                    $this->lexComment();
                    break;
                case self::STATE_DATA:
                    if (null !== $lastToken && $this->cursor === $lastToken['position']) {
                        $this->lexStart();
                    } else {
                        $this->lexData();
                    }
                    break;
                case self::STATE_DQ_STRING:
                    $this->lexDqString();
                    break;
                case self::STATE_INTERPOLATION:
                    $this->lexInterpolation();
                    break;
            }
        }

        if (self::STATE_DATA !== $this->getState()) {
            throw new SyntaxError('Error Processing Request.');
        }

        $this->pushToken(Token::EOF_TYPE);

        return $this->tokens;
    }

    /**
     * @param Source $source
     *
     * @return void
     */
    private function resetState(Source $source): void
    {
        $this->cursor = 0;
        $this->line = 1;
        $this->currentPosition = 0;
        $this->tokens = [];
        $this->state = [];
        $this->bracketsAndTernary = [];

        $this->code = str_replace(["\r\n", "\r"], "\n", $source->getCode());
        $this->end = strlen($this->code);
        $this->filename = $source->getName();
    }

    /**
     * @return int
     *
     * @psalm-return 0|1|2|3|4|5
     */
    private function getState(): int
    {
        return count($this->state) > 0 ? $this->state[count($this->state) - 1][0] : self::STATE_DATA;
    }

    /**
     * @param int                   $state
     * @param array<string, string> $data
     *
     * @return void
     *
     * @psalm-param 0|1|2|3|4|5 $state
     */
    private function pushState(int $state, array $data = []): void
    {
        $this->state[] = [$state, $data];
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    private function setStateParam(string $name, string $value): void
    {
        if ([] === $this->state) {
            throw new LogicException('Cannot update state without a current state.');
        }

        $this->state[count($this->state) - 1][1][$name] = $value;
    }

    /**
     * @return array<string, string>
     */
    private function getStateParams(): array
    {
        return count($this->state) > 0 ? $this->state[count($this->state) - 1][1] : [];
    }

    /**
     * @return void
     */
    private function popState(): void
    {
        if ([] === $this->state) {
            throw new LogicException('Cannot pop state without a current state.');
        }
        array_pop($this->state);
    }

    /**
     * @param string $code
     *
     * @return void
     */
    private function preflightSource(string $code): void
    {
        $tokenPositions = [];
        preg_match_all(self::REGEX_EXPRESSION_START, $code, $tokenPositions, PREG_OFFSET_CAPTURE);
        /** @var array<0|1|2, array<0|1|2|3|4, array{string, int}>> $tokenPositions */

        $tokenPositionsReworked = [];
        foreach ($tokenPositions[0] as $index => $tokenFullMatch) {
            $tokenPositionsReworked[$index] = [
                'fullMatch' => $tokenFullMatch[0],
                'position'  => $tokenFullMatch[1],
                'match'     => $tokenPositions[1][$index][0],
            ];
        }

        $this->tokenPositions = $tokenPositionsReworked;
    }

    /**
     * @param int $offset
     *
     * @return array{fullMatch: string, position: int, match: string}|null
     */
    private function getTokenPosition(int $offset = 0): ?array
    {
        if (
            [] === $this->tokenPositions
            || !isset($this->tokenPositions[$this->currentPosition + $offset])
        ) {
            return null;
        }

        return $this->tokenPositions[$this->currentPosition + $offset];
    }

    /**
     * @param int $value
     *
     * @return void
     */
    private function moveCurrentPosition(int $value = 1): void
    {
        $this->currentPosition += $value;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    private function moveCursor(string $value): void
    {
        $this->cursor += strlen($value);
        $this->line += substr_count($value, "\n");
    }

    /**
     * @param int         $type
     * @param string|null $value
     * @param Token|null  $relatedToken
     *
     * @return Token
     */
    private function pushToken(int $type, string $value = null, ?Token $relatedToken = null): Token
    {
        $strrpos = strrpos(substr($this->code, 0, $this->cursor), PHP_EOL);
        if (false === $strrpos) {
            $strrpos = 0;
        }

        $token = new Token(
            $type,
            $this->line,
            $this->cursor - $strrpos,
            $this->filename,
            $value,
            $relatedToken
        );
        $this->tokens[] = $token;

        return $token;
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexExpression(): void
    {
        $currentCode = $this->code[$this->cursor];
        $nextToken = $this->code[$this->cursor + 1] ?? null;

        if (1 === preg_match('/\t/', $currentCode)) {
            $this->lexTab();
        } elseif (' ' === $currentCode) {
            $this->lexWhitespace();
        } elseif (PHP_EOL === $currentCode) {
            $this->lexEOL();
        } elseif ('=' === $currentCode && '>' === $nextToken) {
            $this->lexArrowFunction();
        } elseif (1 === preg_match($this->operatorRegex, $this->code, $match, 0, $this->cursor)) {
            $this->lexOperator($match[0]);
        } elseif (1 === preg_match(self::REGEX_NAME, $this->code, $match, 0, $this->cursor)) {
            $this->lexName($match[0]);
        } elseif (1 === preg_match(self::REGEX_NUMBER, $this->code, $match, 0, $this->cursor)) {
            $this->lexNumber($match[0]);
        } elseif (in_array($currentCode, ['(', ')', '[', ']', '{', '}', ':', '.', ',', '|'], true)) {
            $this->lexPunctuation();
        } elseif (1 === preg_match(self::REGEX_STRING, $this->code, $match, 0, $this->cursor)) {
            $this->lexString($match[0]);
        } elseif (1 === preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, 0, $this->cursor)) {
            $this->lexStartDqString();
        } else {
            throw new SyntaxError(sprintf('Unexpected character "%s".', $currentCode), $this->line);
        }
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexBlock(): void
    {
        preg_match(self::REGEX_BLOCK_END, $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor);
        /** @var array<int, array{string, int}> $match */

        if ([] === $this->bracketsAndTernary && isset($match[0])) {
            $this->pushToken(Token::BLOCK_END_TYPE, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexVariable(): void
    {
        preg_match(self::REGEX_VAR_END, $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor);
        /** @var array<int, array{string, int}> $match */

        if ([] === $this->bracketsAndTernary && isset($match[0])) {
            $this->pushToken(Token::VAR_END_TYPE, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexComment(): void
    {
        preg_match(self::REGEX_COMMENT_END, $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor);
        /** @var array<int, array{string, int}> $match */

        if (!isset($match[0])) {
            throw new SyntaxError('Unclosed comment.', $this->line);
        }
        if ($match[0][1] === $this->cursor) {
            $this->pushToken(Token::COMMENT_END_TYPE, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            // Parse as text until the end position.
            $this->lexData($match[0][1]);
        }
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexDqString(): void
    {
        if (1 === preg_match(self::REGEX_INTERPOLATION_START, $this->code, $match, 0, $this->cursor)) {
            $this->lexStartInterpolation();
        } elseif (
            1 === preg_match(self::REGEX_DQ_STRING_PART, $this->code, $match, 0, $this->cursor)
            && strlen($match[0]) > 0
        ) {
            $this->pushToken(Token::STRING_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        } elseif (1 === preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, 0, $this->cursor)) {
            $bracket = array_pop($this->bracketsAndTernary);
            $this->popState();
            $this->pushToken(Token::DQ_STRING_END_TYPE, $match[0], $bracket);
            $this->moveCursor($match[0]);
        } else {
            throw new SyntaxError(sprintf('Unexpected character "%s".', $this->code[$this->cursor]), $this->line);
        }
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexInterpolation(): void
    {
        $bracket = end($this->bracketsAndTernary);

        if (
            false !== $bracket
            && '#{' === $bracket->getValue()
            && 1 === preg_match(self::REGEX_INTERPOLATION_END, $this->code, $match, 0, $this->cursor)
        ) {
            $bracket = array_pop($this->bracketsAndTernary);
            $this->pushToken(Token::INTERPOLATION_END_TYPE, $match[0], $bracket);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @param int $limit
     *
     * @return void
     */
    private function lexData(int $limit = 0): void
    {
        $nextToken = $this->getTokenPosition();
        if (0 === $limit && null !== $nextToken) {
            $limit = $nextToken['position'];
        }

        $currentCode = $this->code[$this->cursor];
        if (1 === preg_match('/\t/', $currentCode)) {
            $this->lexTab();
        } elseif (' ' === $currentCode) {
            $this->lexWhitespace();
        } elseif (PHP_EOL === $currentCode) {
            $this->lexEOL();
        } elseif (1 === preg_match('/\S+/', $this->code, $match, 0, $this->cursor)) {
            $value = $match[0];

            // Stop if cursor reaches the next token start.
            if (0 !== $limit && $limit <= ($this->cursor + strlen($value))) {
                $value = substr($value, 0, $limit - $this->cursor);
            }

            // Fixing token start among expressions and comments.
            $nbTokenStart = preg_match_all(self::REGEX_EXPRESSION_START, $value, $matches);
            if ($nbTokenStart > 0) {
                $this->moveCurrentPosition($nbTokenStart);
            }

            if (self::STATE_COMMENT === $this->getState()) {
                $this->pushToken(Token::COMMENT_TEXT_TYPE, $value);
            } else {
                $this->pushToken(Token::TEXT_TYPE, $value);
            }

            $this->moveCursor($value);
        }
    }

    /**
     * @return void
     */
    private function lexStart(): void
    {
        $tokenStart = $this->getTokenPosition();
        \assert(null !== $tokenStart);

        if ('{#' === $tokenStart['match']) {
            $state = self::STATE_COMMENT;
            $tokenType = Token::COMMENT_START_TYPE;
        } elseif ('{%' === $tokenStart['match']) {
            $state = self::STATE_BLOCK;
            $tokenType = Token::BLOCK_START_TYPE;
        } elseif ('{{' === $tokenStart['match']) {
            $state = self::STATE_VAR;
            $tokenType = Token::VAR_START_TYPE;
        } else {
            throw new LogicException(sprintf('Unhandled tag "%s" in lexStart.', $tokenStart['match']));
        }

        $this->pushToken($tokenType, $tokenStart['fullMatch']);
        $this->pushState($state);
        $this->moveCursor($tokenStart['fullMatch']);
    }

    /**
     * @return void
     */
    private function lexStartDqString(): void
    {
        $token = $this->pushToken(Token::DQ_STRING_START_TYPE, '"');
        $this->pushState(self::STATE_DQ_STRING);
        $this->moveCursor('"');
        $this->bracketsAndTernary[] = $token;
    }

    /**
     * @return void
     */
    private function lexStartInterpolation(): void
    {
        $token = $this->pushToken(Token::INTERPOLATION_START_TYPE, '#{');
        $this->pushState(self::STATE_INTERPOLATION);
        $this->moveCursor('#{');
        $this->bracketsAndTernary[] = $token;
    }

    /**
     * @return void
     */
    private function lexTab(): void
    {
        $currentCode = $this->code[$this->cursor];
        $whitespace = '';

        while (preg_match('/\t/', $currentCode)) {
            $whitespace .= $currentCode;
            $this->moveCursor($currentCode);
            $currentCode = $this->code[$this->cursor];
        }

        if (self::STATE_COMMENT === $this->getState()) {
            $this->pushToken(Token::COMMENT_TAB_TYPE, $whitespace);
        } else {
            $this->pushToken(Token::TAB_TYPE, $whitespace);
        }
    }

    /**
     * @return void
     */
    private function lexWhitespace(): void
    {
        $currentCode = $this->code[$this->cursor];
        $whitespace = '';

        while (' ' === $currentCode) {
            $whitespace .= $currentCode;
            $this->moveCursor($currentCode);
            $currentCode = $this->code[$this->cursor];
        }

        if (self::STATE_COMMENT === $this->getState()) {
            $this->pushToken(Token::COMMENT_WHITESPACE_TYPE, $whitespace);
        } else {
            $this->pushToken(Token::WHITESPACE_TYPE, $whitespace);
        }
    }

    /**
     * @return void
     */
    private function lexEOL(): void
    {
        if (self::STATE_COMMENT === $this->getState()) {
            $this->pushToken(Token::COMMENT_EOL_TYPE, $this->code[$this->cursor]);
        } else {
            $this->pushToken(Token::EOL_TYPE, $this->code[$this->cursor]);
        }

        $this->moveCursor($this->code[$this->cursor]);
    }

    /**
     * @return void
     */
    private function lexArrowFunction(): void
    {
        $this->pushToken(Token::ARROW_TYPE, '=>');
        $this->moveCursor('=>');
    }

    /**
     * @param string $operator
     *
     * @return void
     */
    private function lexOperator(string $operator): void
    {
        if ('?' === $operator) {
            $token = $this->pushToken(Token::OPERATOR_TYPE, $operator);
            $this->bracketsAndTernary[] = $token;
        } elseif (':' === $operator) {
            $ternary = array_pop($this->bracketsAndTernary);
            $this->pushToken(Token::OPERATOR_TYPE, $operator, $ternary);
        } else {
            $this->pushToken(Token::OPERATOR_TYPE, $operator);
        }

        $this->moveCursor($operator);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    private function lexName(string $name): void
    {
        if (self::STATE_BLOCK === $this->getState() && !isset($this->getStateParams()['tag'])) {
            $this->pushToken(Token::BLOCK_TAG_TYPE, $name);
            $this->setStateParam('tag', $name);
        } else {
            $this->pushToken(Token::NAME_TYPE, $name);
        }

        $this->moveCursor($name);
    }

    /**
     * @param string $numberAsString
     *
     * @return void
     */
    private function lexNumber(string $numberAsString): void
    {
        $this->pushToken(Token::NUMBER_TYPE, $numberAsString);
        $this->moveCursor($numberAsString);
    }

    /**
     * @return void
     *
     * @throws SyntaxError
     */
    private function lexPunctuation(): void
    {
        $currentCode = $this->code[$this->cursor];

        $lastBracket = end($this->bracketsAndTernary);
        if (false !== $lastBracket && '?' === $lastBracket->getValue()) {
            if (':' === $currentCode) {
                // This is a ternary instead
                $this->lexOperator($currentCode);

                return;
            }
            if (in_array($currentCode, [',', ')', ']', '}'], true)) {
                // Because {{ foo ? 'yes' }} is the same as {{ foo ? 'yes' : '' }}
                do {
                    array_pop($this->bracketsAndTernary);
                    $lastBracket = end($this->bracketsAndTernary);
                } while (false !== $lastBracket && '?' === $lastBracket->getValue());

                // This is maybe the end of the variable, start again.
                $this->lexVariable();

                return;
            }
        }

        if (in_array($currentCode, ['(', '[', '{'], true)) {
            $token = $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode);
            $this->bracketsAndTernary[] = $token;
        } elseif (in_array($currentCode, [')', ']', '}'], true)) {
            if ([] === $this->bracketsAndTernary) {
                throw new SyntaxError(sprintf('Unexpected "%s".', $currentCode), $this->line);
            }

            $bracket = array_pop($this->bracketsAndTernary);
            if (strtr($bracket->getValue(), '([{', ')]}') !== $currentCode) {
                throw new SyntaxError(
                    sprintf('Unclosed "%s".', $bracket->getValue()),
                    $bracket->getLine()
                );
            }

            $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode, $bracket);
        } else {
            $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode);
        }

        $this->moveCursor($currentCode);
    }

    /**
     * @param string $string
     *
     * @return void
     */
    private function lexString(string $string): void
    {
        $this->pushToken(Token::STRING_TYPE, $string);
        $this->moveCursor($string);
    }

    /**
     * @param Environment $env
     *
     * @return string
     */
    private function getOperatorRegex(Environment $env): string
    {
        /** @psalm-suppress InternalMethod */
        $unaryOperators = $env->getUnaryOperators();
        /** @psalm-suppress InternalMethod */
        $binaryOperators = $env->getBinaryOperators();

        /** @var string[] $operators */
        $operators = array_merge(
            ['=', '?', '?:'],
            array_keys($unaryOperators),
            array_keys($binaryOperators)
        );

        $lengthByOperator = [];
        foreach ($operators as $operator) {
            $lengthByOperator[$operator] = strlen($operator);
        }
        arsort($lengthByOperator);

        $regex = [];
        foreach ($lengthByOperator as $operator => $length) {
            if (ctype_alpha($operator[$length - 1])) {
                $r = preg_quote($operator, '/').'(?=[\s()])';
            } else {
                $r = preg_quote($operator, '/');
            }

            $r = preg_replace('/\s+/', '\s+', $r);

            $regex[] = $r;
        }

        return '/'.implode('|', $regex).'/A';
    }
}
