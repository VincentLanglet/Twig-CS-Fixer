<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;

/**
 * Ensures that files have no trailing spaces.
 */
final class TrailingSpaceRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$token->isMatching(Token::EOL_TOKENS)) {
            return;
        }

        if (
            !isset($tokens[$tokenPosition - 1])
            || !$tokens[$tokenPosition - 1]->isMatching(Token::INDENT_TOKENS)
        ) {
            return;
        }

        $fixer = $this->addFixableError(
            'A line should not end with blank space(s).',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($tokenPosition - 1, '');
    }
}
