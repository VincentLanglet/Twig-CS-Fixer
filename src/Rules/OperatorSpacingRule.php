<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensure there is one space before and after an operator except for '..'.
 */
final class OperatorSpacingRule extends AbstractSpacingRule
{
    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::OPERATOR_TYPE)) {
            return null;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, ['not', '-', '+'])) {
            return $this->isUnary($tokenPosition, $tokens) ? null : 1;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, '..')) {
            return 0;
        }

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, ':')) {
            $relatedToken = $token->getRelatedToken();

            return null !== $relatedToken && '?' === $relatedToken->getValue() ? 1 : 0;
        }

        return 1;
    }

    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
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

        if ($this->isTokenMatching($token, Token::OPERATOR_TYPE, ':')) {
            $relatedToken = $token->getRelatedToken();

            return null !== $relatedToken && '?' === $relatedToken->getValue() ? 1 : 0;
        }

        return 1;
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function isUnary(int $tokenPosition, array $tokens): bool
    {
        $previous = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previous, 'An OPERATOR_TYPE cannot be the first non-empty token');

        $previousToken = $tokens[$previous];

        // {{ 1 * -2 }}
        return $this->isTokenMatching($previousToken, Token::OPERATOR_TYPE)
            // {{ -2 }}
            || $this->isTokenMatching($previousToken, Token::VAR_START_TYPE)
            // {{ 1 + (-2) }}
            || $this->isTokenMatching($previousToken, Token::PUNCTUATION_TYPE, ['(', '[', ':', ','])
            // {% if -2 ... %}
            || $this->isTokenMatching($previousToken, Token::BLOCK_NAME_TYPE);
    }
}
