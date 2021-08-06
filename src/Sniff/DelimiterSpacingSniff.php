<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure there is one space before {{, {%, {#, and after }}, %} and #}.
 */
final class DelimiterSpacingSniff extends AbstractSpacingSniff
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

        if (
            $this->isTokenMatching($token, Token::VAR_END_TYPE)
            || $this->isTokenMatching($token, Token::BLOCK_END_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_END_TYPE)
        ) {
            return 1;
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

        if (
            $this->isTokenMatching($token, Token::VAR_START_TYPE)
            || $this->isTokenMatching($token, Token::BLOCK_START_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_START_TYPE)
        ) {
            return 1;
        }

        return null;
    }
}
