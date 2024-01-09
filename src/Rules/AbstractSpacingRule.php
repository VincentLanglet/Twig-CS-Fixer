<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Token\Token;

/**
 * Ensure there is one space before or after some tokens
 */
abstract class AbstractSpacingRule extends AbstractRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $spaceAfter = $this->getSpaceAfter($tokenPosition, $tokens);
        $spaceBefore = $this->getSpaceBefore($tokenPosition, $tokens);

        if (null !== $spaceAfter) {
            $this->checkSpaceAfter($tokenPosition, $tokens, $spaceAfter);
        }

        if (null !== $spaceBefore) {
            $this->checkSpaceBefore($tokenPosition, $tokens, $spaceBefore);
        }
    }

    /**
     * @param array<int, Token> $tokens
     */
    abstract protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int;

    /**
     * @param array<int, Token> $tokens
     */
    abstract protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int;

    /**
     * @param array<int, Token> $tokens
     */
    private function checkSpaceAfter(int $tokenPosition, array $tokens, int $expected): void
    {
        $token = $tokens[$tokenPosition];

        // Ignore new line
        $next = $this->findNext(Token::INDENT_TOKENS, $tokens, $tokenPosition + 1, true);
        if (false === $next || $this->isTokenMatching($tokens[$next], Token::EOL_TOKENS)) {
            return;
        }

        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TOKENS)) {
            $count = \strlen($tokens[$tokenPosition + 1]->getValue());
        } else {
            $count = 0;
        }

        if ($expected === $count) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('Expecting %d whitespace after "%s"; found %d', $expected, $token->getValue(), $count),
            $token,
            'After'
        );

        if (null === $fixer) {
            return;
        }

        if (0 === $count) {
            $fixer->addContent($tokenPosition, str_repeat(' ', $expected));
        } else {
            $fixer->replaceToken($tokenPosition + 1, str_repeat(' ', $expected));
        }
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function checkSpaceBefore(int $tokenPosition, array $tokens, int $expected): void
    {
        $token = $tokens[$tokenPosition];

        // Ignore new line
        $previous = $this->findPrevious(Token::INDENT_TOKENS, $tokens, $tokenPosition - 1, true);
        if (false === $previous || $this->isTokenMatching($tokens[$previous], Token::EOL_TOKENS)) {
            return;
        }

        if ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::WHITESPACE_TOKENS)) {
            $count = \strlen($tokens[$tokenPosition - 1]->getValue());
        } else {
            $count = 0;
        }

        if ($expected === $count) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('Expecting %d whitespace before "%s"; found %d', $expected, $token->getValue(), $count),
            $token,
            'Before'
        );

        if (null === $fixer) {
            return;
        }

        if (0 === $count) {
            $fixer->addContentBefore($tokenPosition, str_repeat(' ', $expected));
        } else {
            $fixer->replaceToken($tokenPosition - 1, str_repeat(' ', $expected));
        }
    }
}
