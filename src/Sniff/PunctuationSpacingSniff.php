<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensure there is no space before and after a punctuation except for ':' and ','.
 */
final class PunctuationSpacingSniff extends AbstractSpacingSniff implements ConfigurableSniffInterface
{
    private const SPACE_BEFORE = [
        ')' => 0,
        ']' => 0,
        '}' => 0,
        ':' => 0,
        '.' => 0,
        ',' => 0,
        '|' => 0,
    ];
    private const SPACE_AFTER = [
        '(' => 0,
        '[' => 0,
        '{' => 0,
        '.' => 0,
        '|' => 0,
        ':' => 1,
        ',' => 1,
    ];

    /**
     * @var array<string, int|array{default: int|null, byPreviousValue: array<string, int|null>}|null>
     */
    private array $spaceBeforeConfig;

    /**
     * @var array<string, int|array{default: int|null, byNextValue: array<string, int|null>}|null>
     */
    private array $spaceAfterConfig;

    /**
     * @param array<string, int|array{default: int|null, byPreviousValue: array<string, int|null>}|null> $spaceBeforeOverride
     * @param array<string, int|array{default: int|null, byNextValue: array<string, int|null>}|null>     $spaceAfterOverride
     */
    public function __construct(
        array $spaceBeforeOverride = [],
        array $spaceAfterOverride = []
    ) {
        $this->spaceBeforeConfig = array_merge(self::SPACE_BEFORE, $spaceBeforeOverride);
        $this->spaceAfterConfig = array_merge(self::SPACE_AFTER, $spaceAfterOverride);
    }

    public function getConfiguration(): array
    {
        return [
            'before' => $this->spaceBeforeConfig,
            'after'  => $this->spaceAfterConfig,
        ];
    }

    /**
     * @param list<Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE)) {
            return null;
        }

        $result = $this->spaceBeforeConfig[$token->getValue()] ?? null;
        if (!\is_array($result)) {
            return $result;
        }

        $previousPosition = $this->findPrevious(Token::WHITESPACE_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previousPosition, 'A PUNCTUATION_TYPE cannot be the first non-empty token');

        $previous = $tokens[$previousPosition];
        if (\array_key_exists($previous->getValue(), $result['byPreviousValue'])) {
            return $result['byPreviousValue'][$previous->getValue()];
        }

        return $result['default'];
    }

    /**
     * @param list<Token> $tokens
     */
    protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE)) {
            return null;
        }

        $nextPosition = $this->findNext(Token::WHITESPACE_TOKENS, $tokens, $tokenPosition + 1, true);
        Assert::notFalse($nextPosition, 'A PUNCTUATION_TYPE cannot be the last non-empty token');

        // We cannot change spaces after a token, if the next one has a constraint: `[1,2,3,]`.
        if (null !== $this->getSpaceBefore($nextPosition, $tokens)) {
            return null;
        }

        $result = $this->spaceAfterConfig[$token->getValue()] ?? null;
        if (!\is_array($result)) {
            return $result;
        }

        $next = $tokens[$nextPosition];
        if (\array_key_exists($next->getValue(), $result['byNextValue'])) {
            return $result['byNextValue'][$next->getValue()];
        }

        return $result['default'];
    }
}
