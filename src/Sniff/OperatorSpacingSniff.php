<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure there is one space before and after an operator except for '..'.
 */
final class OperatorSpacingSniff extends AbstractSpacingSniff
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
        if (!$this->isTokenMatching($token, Token::OPERATOR_TYPE)) {
            return null;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, ['-', '+'])) {
            return $this->isUnary($tokenPosition, $tokens) ? null : 1;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, '..')) {
            return 0;
        }

        return 1;
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
        if (!$this->isTokenMatching($token, Token::OPERATOR_TYPE)) {
            return null;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, ['-', '+'])) {
            return $this->isUnary($tokenPosition, $tokens) ? 0 : 1;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, '..')) {
            return 0;
        }

        return 1;
    }

    /**
     * @param int               $tokenPosition
     * @param array<int, Token> $tokens
     *
     * @return bool
     */
    private function isUnary(int $tokenPosition, array $tokens): bool
    {
        $previous = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        \assert(false !== $previous); // An OPERATOR_TYPE cannot be the first non-empty token

        $previousToken = $tokens[$previous];

        // {{ 1 * -2 }}
        return $this->isTokenMatching($previousToken, Token::OPERATOR_TYPE)
            // {{ -2 }}
            || $this->isTokenMatching($previousToken, Token::VAR_START_TYPE)
            // {{ 1 + (-2) }}
            || $this->isTokenMatching($previousToken, Token::PUNCTUATION_TYPE, ['(', '[', ':', ','])
            // {% if -2 ... %}
            || $this->isTokenMatching($previousToken, Token::BLOCK_TAG_TYPE);
    }
}
