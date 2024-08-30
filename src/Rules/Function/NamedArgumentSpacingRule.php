<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Function;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures named arguments use no space around `=` and no space before/one space after `:`.
 */
final class NamedArgumentSpacingRule extends AbstractSpacingRule
{
    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if ($token->isMatching(Token::NAMED_ARGUMENT_SEPARATOR_TYPE)) {
            return 0;
        }

        return null;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if ($token->isMatching(Token::NAMED_ARGUMENT_SEPARATOR_TYPE, '=')) {
            return 0;
        }
        if ($token->isMatching(Token::NAMED_ARGUMENT_SEPARATOR_TYPE, ':')) {
            return 1;
        }

        return null;
    }
}
