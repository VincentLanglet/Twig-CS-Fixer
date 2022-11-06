<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use LogicException;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Source;
use Webmozart\Assert\Assert;

/**
 * An override of Twig's Lexer to add whitespace and new line detection.
 */
final class Tokenizer implements TokenizerInterface
{
    private const STATE_DATA = 0;
    private const STATE_BLOCK = 1;
    private const STATE_VAR = 2;
    private const STATE_DQ_STRING = 3;
    private const STATE_INTERPOLATION = 4;
    private const STATE_COMMENT = 5;

    private const SQ_STRING_PART = '[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*';
    private const DQ_STRING_PART = '[^#"\\\\]*(?:(?:\\\\.|#(?!\{))[^#"\\\\]*)*';

    private const REGEX_EXPRESSION_START = '/({%|{#|{{)(-|~)?/';
    private const REGEX_BLOCK_END = '/(?:-|~)?(?:%})/A';
    private const REGEX_COMMENT_END = '/(?:-|~)?(?:#})/'; // Must not be anchored
    private const REGEX_VAR_END = '/(?:-|~)?(?:}})/A';
    private const REGEX_INTERPOLATION_START = '/#{/A';
    private const REGEX_INTERPOLATION_END = '/}/A';

    private const REGEX_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/A';
    private const REGEX_NUMBER = '/[0-9]+(?:\.[0-9]+)?/A';
    private const REGEX_STRING = '/"('.self::DQ_STRING_PART.')"|\'('.self::SQ_STRING_PART.')\'/As';
    private const REGEX_DQ_STRING_PART = '/'.self::DQ_STRING_PART.'/As';
    private const REGEX_DQ_STRING_DELIM = '/"/A';

    private string $operatorRegex;

    private int $cursor = 0;

    private ?int $end = null;

    private int $line = 1;

    private int $currentPosition = 0;

    /**
     * @var list<Token>
     */
    private array $tokens = [];

    /**
     * @var array<int, array{fullMatch: string, position: int, match: string}>
     */
    private array $tokenPositions = [];

    /**
     * @var array<array{int, array<string, string>}>
     *
     * @phpstan-var array<array{0|1|2|3|4|5, array<string, string>}>
     */
    private array $state = [];

    /**
     * @var list<Token>
     */
    private array $bracketsAndTernary = [];

    private string $code = '';

    private string $filename = '';

    public function __construct(Environment $env)
    {
        // Caching the regex.
        $this->operatorRegex = $this->getOperatorRegex($env);
    }

    /**
     * @return list<Token>
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

    private function resetState(Source $source): void
    {
        $this->cursor = 0;
        $this->line = 1;
        $this->currentPosition = 0;
        $this->tokens = [];
        $this->state = [];
        $this->bracketsAndTernary = [];

        $this->code = str_replace(["\r\n", "\r"], "\n", $source->getCode());
        $this->end = \strlen($this->code);
        $this->filename = $source->getName();
    }

    /**
     * @return Token[]
     */
    private function getBrackets(): array
    {
        return array_filter($this->bracketsAndTernary, fn (Token $token): bool => '?' !== $token->getValue());
    }

    private function isInTernary(): bool
    {
        $lastBracket = end($this->bracketsAndTernary);

        return false !== $lastBracket && '?' === $lastBracket->getValue();
    }

    /**
     * @phpstan-return 0|1|2|3|4|5
     */
    private function getState(): int
    {
        return \count($this->state) > 0 ? $this->state[\count($this->state) - 1][0] : self::STATE_DATA;
    }

    /**
     * @param array<string, string> $data
     *
     * @phpstan-param 0|1|2|3|4|5 $state
     */
    private function pushState(int $state, array $data = []): void
    {
        $this->state[] = [$state, $data];
    }

    private function setStateParam(string $name, string $value): void
    {
        Assert::notEmpty($this->state, 'Cannot update state without a current state.');

        $this->state[\count($this->state) - 1][1][$name] = $value;
    }

    /**
     * @return array<string, string>
     */
    private function getStateParams(): array
    {
        return \count($this->state) > 0 ? $this->state[\count($this->state) - 1][1] : [];
    }

    private function popState(): void
    {
        Assert::notEmpty($this->state, 'Cannot pop state without a current state.');

        array_pop($this->state);
    }

    private function preflightSource(string $code): void
    {
        $tokenPositions = [];
        preg_match_all(self::REGEX_EXPRESSION_START, $code, $tokenPositions, \PREG_OFFSET_CAPTURE);
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

    private function moveCurrentPosition(int $value = 1): void
    {
        $this->currentPosition += $value;
    }

    private function moveCursor(string $value): void
    {
        $this->cursor += \strlen($value);
        $this->line += substr_count($value, "\n");
    }

    private function pushToken(int $type, string $value = '', ?Token $relatedToken = null): Token
    {
        $strrpos = strrpos(substr($this->code, 0, $this->cursor), \PHP_EOL);
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
        } elseif (\PHP_EOL === $currentCode) {
            $this->lexEOL();
        } elseif ('=' === $currentCode && '>' === $nextToken) {
            $this->lexArrowFunction();
        } elseif (1 === preg_match($this->operatorRegex, $this->code, $match, 0, $this->cursor)) {
            $this->lexOperator($match[0]);
        } elseif (1 === preg_match(self::REGEX_NAME, $this->code, $match, 0, $this->cursor)) {
            $this->lexName($match[0]);
        } elseif (1 === preg_match(self::REGEX_NUMBER, $this->code, $match, 0, $this->cursor)) {
            $this->lexNumber($match[0]);
        } elseif (\in_array($currentCode, ['(', ')', '[', ']', '{', '}', ':', '.', ',', '|'], true)) {
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
     * @throws SyntaxError
     */
    private function lexBlock(): void
    {
        preg_match(self::REGEX_BLOCK_END, $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor);
        /** @var array<int, array{string, int}> $match */
        if (isset($match[0]) && [] === $this->getBrackets()) {
            $this->bracketsAndTernary = []; // To reset ternary
            $this->pushToken(Token::BLOCK_END_TYPE, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @throws SyntaxError
     */
    private function lexVariable(): void
    {
        preg_match(self::REGEX_VAR_END, $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor);
        /** @var array<int, array{string, int}> $match */
        if (isset($match[0]) && [] === $this->getBrackets()) {
            $this->bracketsAndTernary = []; // To reset ternary
            $this->pushToken(Token::VAR_END_TYPE, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @throws SyntaxError
     */
    private function lexComment(): void
    {
        preg_match(self::REGEX_COMMENT_END, $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor);
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

    private function lexDqString(): void
    {
        if (1 === preg_match(self::REGEX_INTERPOLATION_START, $this->code, $match, 0, $this->cursor)) {
            $this->lexStartInterpolation();
        } elseif (
            1 === preg_match(self::REGEX_DQ_STRING_PART, $this->code, $match, 0, $this->cursor)
            && '' !== $match[0]
        ) {
            $this->pushToken(Token::STRING_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        } elseif (1 === preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, 0, $this->cursor)) {
            $bracket = array_pop($this->bracketsAndTernary);
            $this->popState();
            $this->pushToken(Token::DQ_STRING_END_TYPE, $match[0], $bracket);
            $this->moveCursor($match[0]);
        } else {
            // @codeCoverageIgnoreStart
            throw new LogicException(sprintf('Unhandled character "%s" in lexDqString.', $this->code[$this->cursor]));
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @throws SyntaxError
     */
    private function lexInterpolation(): void
    {
        $bracket = end($this->bracketsAndTernary);
        Assert::notFalse($bracket, 'Interpolation always start with a bracket.');

        if (
            '#{' === $bracket->getValue()
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
        } elseif (\PHP_EOL === $currentCode) {
            $this->lexEOL();
        } elseif (1 === preg_match('/\S+/', $this->code, $match, 0, $this->cursor)) {
            $value = $match[0];

            // Stop if cursor reaches the next token start.
            if (0 !== $limit && $limit <= ($this->cursor + \strlen($value))) {
                $value = substr($value, 0, $limit - $this->cursor);
            }

            if (self::STATE_COMMENT === $this->getState()) {
                $this->pushToken(Token::COMMENT_TEXT_TYPE, $value);
            } else {
                $this->pushToken(Token::TEXT_TYPE, $value);
            }

            $this->moveCursor($value);
        }
    }

    private function lexStart(): void
    {
        $tokenStart = $this->getTokenPosition();
        Assert::notNull($tokenStart, 'There is no token to lex.');

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
            // @codeCoverageIgnoreStart
            throw new LogicException(sprintf('Unhandled tag "%s" in lexStart.', $tokenStart['match']));
            // @codeCoverageIgnoreEnd
        }

        $this->pushToken($tokenType, $tokenStart['fullMatch']);
        $this->pushState($state);
        $this->moveCursor($tokenStart['fullMatch']);
    }

    private function lexStartDqString(): void
    {
        $token = $this->pushToken(Token::DQ_STRING_START_TYPE, '"');
        $this->pushState(self::STATE_DQ_STRING);
        $this->moveCursor('"');
        $this->bracketsAndTernary[] = $token;
    }

    private function lexStartInterpolation(): void
    {
        $token = $this->pushToken(Token::INTERPOLATION_START_TYPE, '#{');
        $this->pushState(self::STATE_INTERPOLATION);
        $this->moveCursor('#{');
        $this->bracketsAndTernary[] = $token;
    }

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

    private function lexEOL(): void
    {
        if (self::STATE_COMMENT === $this->getState()) {
            $this->pushToken(Token::COMMENT_EOL_TYPE, $this->code[$this->cursor]);
        } else {
            $this->pushToken(Token::EOL_TYPE, $this->code[$this->cursor]);
        }

        $this->moveCursor($this->code[$this->cursor]);
    }

    private function lexArrowFunction(): void
    {
        $this->pushToken(Token::ARROW_TYPE, '=>');
        $this->moveCursor('=>');
    }

    private function lexOperator(string $operator): void
    {
        if ('?' === $operator) {
            $token = $this->pushToken(Token::OPERATOR_TYPE, $operator);
            $this->bracketsAndTernary[] = $token;
        } elseif (':' === $operator && $this->isInTernary()) {
            $bracket = array_pop($this->bracketsAndTernary);
            $this->pushToken(Token::OPERATOR_TYPE, $operator, $bracket);
        } else {
            $this->pushToken(Token::OPERATOR_TYPE, $operator);
        }

        $this->moveCursor($operator);
    }

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

    private function lexNumber(string $numberAsString): void
    {
        $this->pushToken(Token::NUMBER_TYPE, $numberAsString);
        $this->moveCursor($numberAsString);
    }

    /**
     * @throws SyntaxError
     */
    private function lexPunctuation(): void
    {
        $currentCode = $this->code[$this->cursor];

        if ($this->isInTernary()) {
            if (':' === $currentCode) {
                // This is a ternary instead
                $this->lexOperator($currentCode);

                return;
            }
            if (\in_array($currentCode, [',', ')', ']', '}'], true)) {
                // Because {{ foo ? 'yes' }} is the same as {{ foo ? 'yes' : '' }}
                do {
                    array_pop($this->bracketsAndTernary);
                    $lastBracket = end($this->bracketsAndTernary);
                } while (false !== $lastBracket && '?' === $lastBracket->getValue());

                // This is maybe the end of the expression so start again.
                return;
            }
        }

        $lastBracket = end($this->bracketsAndTernary);
        if (':' === $currentCode && false !== $lastBracket && '[' === $lastBracket->getValue()) {
            // This is a slice shortcut '[0:1]' instead
            $this->lexOperator($currentCode);

            return;
        }

        if (\in_array($currentCode, ['(', '[', '{'], true)) {
            $token = $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode);
            $this->bracketsAndTernary[] = $token;
        } elseif (\in_array($currentCode, [')', ']', '}'], true)) {
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

        // Avoid parsing object key with operator name like `foo.in` as an operator
        if (
            '.' === $currentCode
            && 1 === preg_match(self::REGEX_NAME, $this->code, $match, 0, $this->cursor)
        ) {
            $this->lexName($match[0]);
        }
    }

    private function lexString(string $string): void
    {
        $this->pushToken(Token::STRING_TYPE, $string);
        $this->moveCursor($string);
    }

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
            $lengthByOperator[$operator] = \strlen($operator);
        }
        arsort($lengthByOperator);

        $regex = [];
        foreach ($lengthByOperator as $operator => $length) {
            // An operator that ends with a character must be followed by
            // a whitespace, a parenthesis, an opening map [ or sequence {
            $r = preg_quote($operator, '/');
            if (ctype_alpha($operator[$length - 1])) {
                $r .= '(?=[\s()\[{])';
            }

            // An operator that begins with a character must not have a dot or pipe before
            if (ctype_alpha($operator[0])) {
                $r = '(?<![\.\|])'.$r;
            }

            // An operator with a space can be any amount of whitespaces
            $r = preg_replace('/\s+/', '\s+', $r);

            $regex[] = $r;
        }

        return '/'.implode('|', $regex).'/A';
    }
}
