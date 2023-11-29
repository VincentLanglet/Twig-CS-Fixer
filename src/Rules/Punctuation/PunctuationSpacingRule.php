<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Punctuation;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensure there is no space before and after a punctuation except for ':' and ','.
 */
final class PunctuationSpacingRule extends AbstractSpacingRule
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
     * @param array<int, Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE)) {
            return null;
        }

        return self::SPACE_BEFORE[$token->getValue()] ?? null;
    }

    /**
     * @param array<int, Token> $tokens
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

        return self::SPACE_AFTER[$token->getValue()] ?? null;
    }
}
