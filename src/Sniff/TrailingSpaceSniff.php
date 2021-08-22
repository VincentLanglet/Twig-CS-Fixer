<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use Exception;
use TwigCsFixer\Token\Token;

/**
 * Ensure that files has no trailing space.
 */
final class TrailingSpaceSniff extends AbstractSniff
{
    /**
     * @param int               $tokenPosition
     * @param array<int, Token> $tokens
     *
     * @return void
     *
     * @throws Exception
     */
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::EOL_TOKENS)) {
            return;
        }

        $previousToken = $tokens[$tokenPosition - 1] ?? null;
        if (
            null === $previousToken
            || !$this->isTokenMatching($previousToken, Token::WHITESPACE_TOKENS + Token::TAB_TOKENS)
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
