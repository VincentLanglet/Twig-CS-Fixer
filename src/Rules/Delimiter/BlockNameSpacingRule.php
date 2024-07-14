<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Delimiter;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is one space before and after block names.
 */
final class BlockNameSpacingRule extends AbstractSpacingRule
{
    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if ($token->isMatching(Token::BLOCK_NAME_TYPE)) {
            return 1;
        }

        return null;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if ($token->isMatching(Token::BLOCK_NAME_TYPE)) {
            return 1;
        }

        return null;
    }
}
