<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Environment;
use Twig\Source;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\ViolationId;
use Webmozart\Assert\Assert;

/**
 * An override of Twig\Lexer to add whitespace and new line detection.
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

    private const REGEX_EXPRESSION_START = '/({%|{#|{{)[-~]?/';
    private const REGEX_BLOCK_END = '/[-~]?%}/A';
    private const REGEX_COMMENT_END = '/[-~]?#}/'; // Must not be anchored
    private const REGEX_VAR_END = '/[-~]?}}/A';
    private const REGEX_VERBATIM_END = '/{%[-~]?\s*?endverbatim\s*?[-~]?%}/A';
    private const REGEX_INTERPOLATION_START = '/#{/A';
    private const REGEX_INTERPOLATION_END = '/}/A';

    private const REGEX_NAME = '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/A';
    private const REGEX_NUMBER = '/[0-9]+(?:\.[0-9]+)?([Ee][+\-][0-9]+)?/A';
    private const REGEX_STRING = '/"('.self::DQ_STRING_PART.')"|\'('.self::SQ_STRING_PART.')\'/As';
    private const REGEX_DQ_STRING_PART = '/'.self::DQ_STRING_PART.'/As';
    private const REGEX_DQ_STRING_DELIM = '/"/A';

    /**
     * @var non-empty-string
     */
    private string $operatorRegex;

    private int $cursor = 0;

    private int $lastEOL = 0;

    private ?int $end = null;

    private int $line = 1;

    private int $currentExpressionStarter = 0;

    /**
     * @var list<Token>
     */
    private array $tokens = [];

    /**
     * @var list<ViolationId>
     */
    private array $ignoredViolations = [];

    /**
     * @var list<array{fullMatch: string, position: int, match: string}>
     */
    private array $expressionStarters = [];

    /**
     * @var array<array{int<0, 5>, array<string, string|null>}>
     */
    private array $state = [];

    /**
     * @var list<Token>
     */
    private array $bracketsAndTernary = [];

    private bool $isVerbatim = false;

    private string $code = '';

    private string $filename = '';

    public function __construct(Environment $env)
    {
        // Caching the regex.
        $this->operatorRegex = $this->getOperatorRegex($env);
    }

    /**
     * @return array{list<Token>, list<ViolationId>}
     *
     * @throws CannotTokenizeException
     */
    public function tokenize(Source $source): array
    {
        $this->resetState($source);
        $this->pushState(self::STATE_DATA);
        $this->preflightSource($this->code);

        $oldCursor = $this->cursor;
        $oldCurrentExpressionStarter = $this->currentExpressionStarter;
        $oldBracketAndTernary = $this->bracketsAndTernary;

        while ($this->cursor < $this->end) {
            switch ($this->getState()) {
                case self::STATE_DATA:
                    if (
                        !$this->hasExpressionStarter()
                        || $this->cursor < $this->getExpressionStarter()['position']
                    ) {
                        $this->lexData();
                    } elseif ($this->cursor === $this->getExpressionStarter()['position']) {
                        $this->lexStart();
                    } else {
                        $this->moveCurrentExpressionStarter();
                    }
                    break;
                case self::STATE_BLOCK:
                    $this->lexBlock();
                    break;
                case self::STATE_VAR:
                    $this->lexVariable();
                    break;
                case self::STATE_DQ_STRING:
                    $this->lexDqString();
                    break;
                case self::STATE_INTERPOLATION:
                    $this->lexInterpolation();
                    break;
                case self::STATE_COMMENT:
                    $this->lexComment();
                    break;
            }

            if (
                $oldCursor === $this->cursor
                && $oldCurrentExpressionStarter === $this->currentExpressionStarter
                && $oldBracketAndTernary === $this->bracketsAndTernary
            ) {
                // @codeCoverageIgnoreStart
                throw new \LogicException('Infinite loop');
                // @codeCoverageIgnoreEnd
            }

            $oldCursor = $this->cursor;
            $oldCurrentExpressionStarter = $this->currentExpressionStarter;
            $oldBracketAndTernary = $this->bracketsAndTernary;
        }

        if (self::STATE_DATA !== $this->getState()) {
            throw CannotTokenizeException::unknownError();
        }

        $this->pushToken(Token::EOF_TYPE);

        return [$this->tokens, $this->ignoredViolations];
    }

    private function resetState(Source $source): void
    {
        $this->cursor = 0;
        $this->lastEOL = 0;
        $this->line = 1;
        $this->currentExpressionStarter = 0;
        $this->tokens = [];
        $this->state = [];
        $this->bracketsAndTernary = [];

        $this->code = $source->getCode();
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
     * @return int<0, 5>
     */
    private function getState(): int
    {
        Assert::notEmpty($this->state, 'No state was pushed.');

        return $this->state[\count($this->state) - 1][0];
    }

    /**
     * @param int<0, 5> $state
     */
    private function pushState(int $state): void
    {
        $this->state[] = [$state, []];
    }

    /**
     * @psalm-suppress PossiblyInvalidArrayAssignment, PropertyTypeCoercion
     *
     * @see https://github.com/vimeo/psalm/issues/8989
     */
    private function setStateParam(string $name, ?string $value): void
    {
        Assert::notEmpty($this->state, 'Cannot set state params without a current state.');

        $this->state[\count($this->state) - 1][1][$name] = $value;
    }

    private function getStateParam(string $name): ?string
    {
        Assert::notEmpty($this->state, 'Cannot get state params without a current state.');

        return $this->state[\count($this->state) - 1][1][$name];
    }

    private function hasStateParam(string $name): bool
    {
        Assert::notEmpty($this->state, 'Cannot check state params without a current state.');

        return \array_key_exists($name, $this->state[\count($this->state) - 1][1]);
    }

    private function popState(): void
    {
        Assert::notEmpty($this->state, 'Cannot pop state without a current state.');

        array_pop($this->state);
    }

    private function preflightSource(string $code): void
    {
        preg_match_all(self::REGEX_EXPRESSION_START, $code, $match, \PREG_OFFSET_CAPTURE);

        $expressionStartersReworked = [];
        foreach ($match[0] as $index => $tokenFullMatch) {
            $expressionStartersReworked[] = [
                'fullMatch' => $tokenFullMatch[0],
                'position' => $tokenFullMatch[1],
                'match' => $match[1][$index][0],
            ];
        }

        $this->expressionStarters = $expressionStartersReworked;
    }

    private function hasExpressionStarter(): bool
    {
        return isset($this->expressionStarters[$this->currentExpressionStarter]);
    }

    /**
     * @return array{fullMatch: string, position: int, match: string}
     */
    private function getExpressionStarter(): array
    {
        Assert::true($this->hasExpressionStarter(), 'There is no more expression starters');

        return $this->expressionStarters[$this->currentExpressionStarter];
    }

    private function moveCurrentExpressionStarter(): void
    {
        Assert::true($this->hasExpressionStarter(), 'There is no more expression starters');

        ++$this->currentExpressionStarter;
    }

    private function pushToken(int|string $type, string $value = '', ?Token $relatedToken = null): Token
    {
        $token = new Token(
            $type,
            $this->line,
            $this->cursor + 1 - $this->lastEOL,
            $this->filename,
            $value,
            $relatedToken
        );
        $this->tokens[] = $token;

        $this->cursor += \strlen($value);
        $this->line += substr_count($value, "\n");

        return $token;
    }

    /**
     * @throws CannotTokenizeException
     */
    private function lexExpression(): void
    {
        $currentCode = $this->code[$this->cursor];
        $nextToken = $this->code[$this->cursor + 1] ?? null;
        $next2Token = $this->code[$this->cursor + 2] ?? null;

        if (1 === preg_match('/\t/', $currentCode)) {
            $this->lexTab();
        } elseif (' ' === $currentCode) {
            $this->lexWhitespace();
        } elseif (1 === preg_match("/\r\n?|\n/", $currentCode)) {
            $this->lexEOL();
        } elseif ('.' === $currentCode && '.' === $nextToken && '.' === $next2Token) {
            $this->lexSpread();
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
            throw CannotTokenizeException::unexpectedCharacter($currentCode, $this->line);
        }
    }

    /**
     * @throws CannotTokenizeException
     */
    private function lexBlock(): void
    {
        preg_match(self::REGEX_BLOCK_END, $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor);

        if (isset($match[0]) && [] === $this->getBrackets()) {
            $this->bracketsAndTernary = []; // To reset ternary
            $this->pushToken(Token::BLOCK_END_TYPE, $match[0][0]);

            $this->isVerbatim = 'verbatim' === $this->getStateParam('blockName');
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @throws CannotTokenizeException
     */
    private function lexVariable(): void
    {
        preg_match(self::REGEX_VAR_END, $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor);

        if (isset($match[0]) && [] === $this->getBrackets()) {
            $this->bracketsAndTernary = []; // To reset ternary
            $this->pushToken(Token::VAR_END_TYPE, $match[0][0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @throws CannotTokenizeException
     */
    private function lexComment(): void
    {
        preg_match(self::REGEX_COMMENT_END, $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor);

        if (!isset($match[0])) {
            throw CannotTokenizeException::unclosedComment($this->line);
        }
        if ($match[0][1] === $this->cursor) {
            $this->processIgnoredViolations();
            $this->pushToken(Token::COMMENT_END_TYPE, $match[0][0]);
            $this->popState();
        } else {
            if (!$this->hasStateParam('ignoredViolations')) {
                $comment = substr($this->code, $this->cursor, $match[0][1]);
                $this->extractIgnoredViolations($comment);
            }

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
        } elseif (1 === preg_match(self::REGEX_DQ_STRING_DELIM, $this->code, $match, 0, $this->cursor)) {
            $bracket = array_pop($this->bracketsAndTernary);
            $this->popState();
            $this->pushToken(Token::DQ_STRING_END_TYPE, $match[0], $bracket);
        } else {
            // @codeCoverageIgnoreStart
            throw new \LogicException(sprintf('Unhandled character "%s" in lexDqString.', $this->code[$this->cursor]));
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @throws CannotTokenizeException
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
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    private function lexData(int $limit = 0): void
    {
        if (0 === $limit && $this->hasExpressionStarter()) {
            $limit = $this->getExpressionStarter()['position'];
        }

        $currentCode = $this->code[$this->cursor];
        if (1 === preg_match('/\t/', $currentCode)) {
            $this->lexTab();
        } elseif (' ' === $currentCode) {
            $this->lexWhitespace();
        } elseif (1 === preg_match("/\r\n?|\n/", $currentCode)) {
            $this->lexEOL();
        } elseif (1 === preg_match('/\S+/', $this->code, $match, 0, $this->cursor)) {
            $value = $match[0];

            // Stop if cursor reaches the next expression starter.
            if (0 !== $limit) {
                $value = substr($value, 0, $limit - $this->cursor);
            }

            if (self::STATE_COMMENT === $this->getState()) {
                $this->pushToken(Token::COMMENT_TEXT_TYPE, $value);
            } else {
                $this->pushToken(Token::TEXT_TYPE, $value);
            }
        }
    }

    private function lexStart(): void
    {
        if (
            $this->isVerbatim
            && 1 !== preg_match(self::REGEX_VERBATIM_END, $this->code, $match, 0, $this->cursor)
        ) {
            // Skip this expression starter since we're still in verbatim mode
            $this->moveCurrentExpressionStarter();

            return;
        }

        $expressionStarter = $this->getExpressionStarter();
        if ('{#' === $expressionStarter['match']) {
            $state = self::STATE_COMMENT;
            $tokenType = Token::COMMENT_START_TYPE;
        } elseif ('{%' === $expressionStarter['match']) {
            $state = self::STATE_BLOCK;
            $tokenType = Token::BLOCK_START_TYPE;
        } elseif ('{{' === $expressionStarter['match']) {
            $state = self::STATE_VAR;
            $tokenType = Token::VAR_START_TYPE;
        } else {
            // @codeCoverageIgnoreStart
            throw new \LogicException(sprintf('Unhandled tag "%s" in lexStart.', $expressionStarter['match']));
            // @codeCoverageIgnoreEnd
        }

        $this->pushToken($tokenType, $expressionStarter['fullMatch']);
        $this->pushState($state);
    }

    private function lexStartDqString(): void
    {
        $token = $this->pushToken(Token::DQ_STRING_START_TYPE, '"');
        $this->pushState(self::STATE_DQ_STRING);
        $this->bracketsAndTernary[] = $token;
    }

    private function lexStartInterpolation(): void
    {
        $token = $this->pushToken(Token::INTERPOLATION_START_TYPE, '#{');
        $this->pushState(self::STATE_INTERPOLATION);
        $this->bracketsAndTernary[] = $token;
    }

    private function lexTab(): void
    {
        $whitespace = '';
        $cursor = $this->cursor;
        while (preg_match('/\t/', $this->code[$cursor])) {
            $whitespace .= $this->code[$cursor];
            ++$cursor;
        }

        if (self::STATE_COMMENT === $this->getState()) {
            $this->pushToken(Token::COMMENT_TAB_TYPE, $whitespace);
        } else {
            $this->pushToken(Token::TAB_TYPE, $whitespace);
        }
    }

    private function lexWhitespace(): void
    {
        $whitespace = '';
        $cursor = $this->cursor;
        while (' ' === $this->code[$cursor]) {
            $whitespace .= $this->code[$cursor];
            ++$cursor;
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

        $this->lastEOL = $this->cursor;
    }

    private function lexSpread(): void
    {
        $this->pushToken(Token::SPREAD_TYPE, '...');
    }

    private function lexArrowFunction(): void
    {
        $this->pushToken(Token::ARROW_TYPE, '=>');
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
    }

    private function lexName(string $name): void
    {
        if (self::STATE_BLOCK === $this->getState() && !$this->hasStateParam('blockName')) {
            $this->pushToken(Token::BLOCK_NAME_TYPE, $name);
            $this->setStateParam('blockName', $name);
        } else {
            $this->pushToken(Token::NAME_TYPE, $name);
        }
    }

    private function lexNumber(string $numberAsString): void
    {
        $this->pushToken(Token::NUMBER_TYPE, $numberAsString);
    }

    /**
     * @throws CannotTokenizeException
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
                // Because {{ (foo ? 'yes') }} is the same as {{ (foo ? 'yes' : '') }}
                array_pop($this->bracketsAndTernary);

                // This is maybe the end of the expression so start again.
                return;
            }
        }

        if (\in_array($currentCode, ['(', '[', '{'], true)) {
            $token = $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode);
            $this->bracketsAndTernary[] = $token;
        } elseif (\in_array($currentCode, [')', ']', '}'], true)) {
            if ([] === $this->bracketsAndTernary) {
                throw CannotTokenizeException::unexpectedCharacter($currentCode, $this->line);
            }

            $bracket = array_pop($this->bracketsAndTernary);
            if (strtr($bracket->getValue(), '([{', ')]}') !== $currentCode) {
                throw CannotTokenizeException::unclosedBracket($bracket->getValue(), $bracket->getLine());
            }

            $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode, $bracket);
        } elseif (':' === $currentCode) {
            if ([] === $this->bracketsAndTernary) {
                throw CannotTokenizeException::unexpectedCharacter($currentCode, $this->line);
            }

            $bracket = end($this->bracketsAndTernary);
            if ('[' === $bracket->getValue()) {
                // This is a slice shortcut '[0:1]' instead
                $this->lexOperator($currentCode);

                return;
            }

            $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode);
        } else {
            $this->pushToken(Token::PUNCTUATION_TYPE, $currentCode);
        }
    }

    private function lexString(string $string): void
    {
        $this->pushToken(Token::STRING_TYPE, $string);
    }

    /**
     * @return non-empty-string
     */
    private function getOperatorRegex(Environment $env): string
    {
        /** @psalm-suppress InternalMethod */
        $unaryOperators = array_keys($env->getUnaryOperators());
        /** @psalm-suppress InternalMethod */
        $binaryOperators = array_keys($env->getBinaryOperators());

        /** @var string[] $operators */
        $operators = ['=', '?', '?:', ...$unaryOperators, ...$binaryOperators];
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
            if (ctype_alpha($operator[-1])) {
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

    private function extractIgnoredViolations(string $comment): void
    {
        $comment = trim($comment);
        if (1 === preg_match('/^twig-cs-fixer-disable(|-line|-next-line)\s+([\s\w,.:]*)/i', $comment, $match)) {
            $this->setStateParam('ignoredViolations', preg_replace('/\s+/', ',', $match[2]) ?? '');
            $this->setStateParam('ignoredType', trim($match[1], '-'));
        } else {
            $this->setStateParam('ignoredViolations', null);
        }
    }

    private function processIgnoredViolations(): void
    {
        $ignoredViolations = $this->getStateParam('ignoredViolations');
        if (null === $ignoredViolations) {
            return;
        }

        $line = match ($this->getStateParam('ignoredType')) {
            'line' => $this->line,
            'next-line' => $this->line + 1,
            default => null,
        };

        if ('' === $ignoredViolations) {
            $this->ignoredViolations[] = ViolationId::fromString($ignoredViolations, $line);

            return;
        }

        $ignoredViolationsExploded = explode(',', $ignoredViolations);
        foreach ($ignoredViolationsExploded as $ignoredViolation) {
            if ('' === $ignoredViolation) {
                continue;
            }
            $this->ignoredViolations[] = ViolationId::fromString($ignoredViolation, $line);
        }
    }
}
