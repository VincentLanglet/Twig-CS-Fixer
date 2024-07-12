<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Delimiter;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Token\Token;

/**
 * Ensures there is one space before and after block names.
 */
final class BlockNameSpacingRule extends AbstractSpacingRule
{
    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];

        if ($token->isMatching(Token::BLOCK_NAME_TYPE)) {
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

        if ($token->isMatching(Token::BLOCK_NAME_TYPE)) {
            return 1;
        }

        return null;
    }
}
