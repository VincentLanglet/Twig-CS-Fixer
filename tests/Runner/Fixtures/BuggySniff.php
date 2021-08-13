<?php

namespace TwigCsFixer\Tests\Runner\Fixtures;

use TwigCsFixer\Sniff\AbstractSpacingSniff;
use TwigCsFixer\Token\Token;

/**
 * This Sniff is buggy because it can't decide how to solve `,]`.
 */
class BuggySniff extends AbstractSpacingSniff
{
    /**
     * @param int               $tokenPosition
     * @param array<int, Token> $tokens
     *
     * @return int|null
     */
    protected function shouldHaveSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [']'])) {
            return 0;
        }

        return null;
    }

    /**
     * @param int               $tokenPosition
     * @param array<int, Token> $tokens
     *
     * @return int|null
     */
    protected function shouldHaveSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [','])) {
            return 1;
        }

        return null;
    }
}
