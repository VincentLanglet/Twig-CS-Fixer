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
    public function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::EOL_TOKENS)) {
            return;
        }

        $previousTokenPosition = $tokenPosition - 1;
        $previousToken = $tokens[$previousTokenPosition] ?? null;
        if (null === $previousToken || !$this->isTokenMatching($previousToken, Token::WHITESPACE_TOKENS)) {
            return;
        }

        $fixer = $this->addFixableError(
            'A line should not end with blank space(s).',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->beginChangeset();

        do {
            $fixer->replaceToken($previousTokenPosition, '');
            $previousTokenPosition--;

            $previousToken = $tokenPositions[$previousTokenPosition] ?? null;
        } while (null !== $previousToken && $this->isTokenMatching($previousToken, Token::WHITESPACE_TOKENS));

        $fixer->endChangeset();
    }
}
