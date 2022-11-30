<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure there is no space before and after a punctuation except for ':' and ','.
 */
final class PunctuationSpacingSniff extends AbstractSpacingSniff
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
     * @param list<Token> $tokens
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
     * @param list<Token> $tokens
     */
    protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE)) {
            return null;
        }

        // We cannot change spaces after a token, if the next one has a constraint: `[1,2,3,]`.
        $nextPosition = $this->findNext(Token::WHITESPACE_TOKENS, $tokens, $tokenPosition + 1, true);
        if (false !== $nextPosition && null !== $this->getSpaceBefore($nextPosition, $tokens)) {
            return null;
        }

        return self::SPACE_AFTER[$token->getValue()] ?? null;
    }
}
