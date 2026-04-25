<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is one space before and after a ternary operator.
 */
final class TernaryOperatorSpacingRule extends AbstractSpacingRule
{
    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::TERNARY_OPERATOR_TYPE)) {
            return null;
        }

        return 1;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::TERNARY_OPERATOR_TYPE)) {
            return null;
        }

        return 1;
    }
}
