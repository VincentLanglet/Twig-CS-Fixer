<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset\Generic;

use TwigCsFixer\Sniff\AbstractSpacingSniff;
use TwigCsFixer\Token\Token;

/**
 * Ensure there is no space before and after a punctuation except for ':' and ','
 */
final class PunctuationSpacingSniff extends AbstractSpacingSniff
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
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [')', ']', '}', ':', '.', ',', '|'])) {
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
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [':', ','])) {
            return 1;
        }

        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, ['(', '[', '{', '.', '|'])) {
            return 0;
        }

        return null;
    }
}
