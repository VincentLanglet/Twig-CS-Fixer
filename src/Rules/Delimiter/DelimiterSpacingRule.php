<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Delimiter;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;

/**
 * Ensures there is one space before '}}', '%}' and '#}', and after '{{', '{%', '{#'.
 */
final class DelimiterSpacingRule extends AbstractSpacingRule
{
    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        if (
            $this->isTokenMatching($token, Token::BLOCK_END_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_END_TYPE)
            || $this->isTokenMatching($token, Token::VAR_END_TYPE)
        ) {
            return 1;
        }

        return null;
    }

    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        if (
            $this->isTokenMatching($token, Token::BLOCK_START_TYPE)
            || $this->isTokenMatching($token, Token::COMMENT_START_TYPE)
            || $this->isTokenMatching($token, Token::VAR_START_TYPE)
        ) {
            return 1;
        }

        return null;
    }
}
