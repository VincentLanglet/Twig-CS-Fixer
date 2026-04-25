<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is one space after `not` and no space after others unary operators.
 */
final class UnaryOperatorSpacingRule extends AbstractSpacingRule
{
    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        return null;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::UNARY_OPERATOR_TYPE)) {
            return null;
        }

        if ('not' === $token->getValue()) {
            return 1;
        }

        return 0;
    }
}
