<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure there is one space before or after some tokens
 */
abstract class AbstractSpacingSniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $spaceAfter = $this->shouldHaveSpaceAfter($tokenPosition, $tokens);
        $spaceBefore = $this->shouldHaveSpaceBefore($tokenPosition, $tokens);

        if (null !== $spaceAfter) {
            $this->checkSpaceAfter($tokenPosition, $tokens, $spaceAfter);
        }

        if (null !== $spaceBefore) {
            $this->checkSpaceBefore($tokenPosition, $tokens, $spaceBefore);
        }
    }

    /**
     * @param list<Token> $tokens
     */
    abstract protected function shouldHaveSpaceAfter(int $tokenPosition, array $tokens): ?int;

    /**
     * @param list<Token> $tokens
     */
    abstract protected function shouldHaveSpaceBefore(int $tokenPosition, array $tokens): ?int;

    /**
     * @param list<Token> $tokens
     */
    private function checkSpaceAfter(int $tokenPosition, array $tokens, int $expected): void
    {
        $token = $tokens[$tokenPosition];

        // Ignore new line
        $next = $this->findNext(Token::WHITESPACE_TOKENS, $tokens, $tokenPosition + 1, true);
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
            $token
        );

        // Only linting currently.
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
     * @param list<Token> $tokens
     */
    private function checkSpaceBefore(int $tokenPosition, array $tokens, int $expected): void
    {
        $token = $tokens[$tokenPosition];

        // Ignore new line
        $previous = $this->findPrevious(Token::WHITESPACE_TOKENS, $tokens, $tokenPosition - 1, true);
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
            $token
        );

        // Only linting currently.
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
