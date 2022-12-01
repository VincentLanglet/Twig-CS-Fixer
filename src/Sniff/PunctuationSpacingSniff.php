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
     * @var array<string, int|null>
     */
    private array $spaceBeforeConfig;

    /**
     * @var array<string, int|null>
     */
    private array $spaceAfterConfig;

    /**
     * @param array<string, int|null> $spaceBeforeOverride
     * @param array<string, int|null> $spaceAfterOverride
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

        return $this->spaceBeforeConfig[$token->getValue()] ?? null;
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

        return $this->spaceAfterConfig[$token->getValue()] ?? null;
    }
}
