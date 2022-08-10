<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner\Fixtures;

use TwigCsFixer\Sniff\AbstractSpacingSniff;
use TwigCsFixer\Token\Token;

/**
 * This Sniff is buggy because it can't decide how to solve `,]`.
 */
class BuggySniff extends AbstractSpacingSniff
{
    /**
     * @param array<int, Token> $tokens
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
     * @param array<int, Token> $tokens
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
