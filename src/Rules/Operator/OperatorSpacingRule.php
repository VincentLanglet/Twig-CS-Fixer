<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensures there is one space before and after an operator except for '..'.
 */
final class OperatorSpacingRule extends AbstractSpacingRule
{
    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if (!$token->isMatching(Token::OPERATOR_TYPE)) {
            return null;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ['not', '-', '+'])) {
            return $this->isUnary($tokenPosition, $tokens) ? null : 1;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, '..')) {
            return 0;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ':')) {
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
        if (!$token->isMatching(Token::OPERATOR_TYPE)) {
            return null;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ['-', '+'])) {
            return $this->isUnary($tokenPosition, $tokens) ? 0 : 1;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, '..')) {
            return 0;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ':')) {
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

        return $previousToken->isMatching([
            // {{ 1 * -2 }}
            Token::OPERATOR_TYPE,
            // {{ -2 }}
            Token::VAR_START_TYPE,
            // {% if -2 ... %}
            Token::BLOCK_NAME_TYPE,
        ])
        // {{ 1 + (-2) }}
        || $previousToken->isMatching(Token::PUNCTUATION_TYPE, ['(', '[', ':', ',']);
    }
}
