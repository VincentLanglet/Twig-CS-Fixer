<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use Exception;
use TwigCsFixer\Token\Token;

use function sprintf;
use function str_repeat;
use function strlen;

/**
 * Ensure there is one space before or after some tokens
 */
abstract class AbstractSpacingSniff extends AbstractSniff
{
    /**
     * @param int         $tokenPosition
     * @param list<Token> $tokens
     *
     * @return void
     *
     * @throws Exception
     */
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
     * @param int         $tokenPosition
     * @param list<Token> $tokens
     *
     * @return int|null
     */
    abstract protected function shouldHaveSpaceAfter(int $tokenPosition, array $tokens): ?int;

    /**
     * @param int         $tokenPosition
     * @param list<Token> $tokens
     *
     * @return int|null
     */
    abstract protected function shouldHaveSpaceBefore(int $tokenPosition, array $tokens): ?int;

    /**
     * @param int         $tokenPosition
     * @param list<Token> $tokens
     * @param int         $expected
     *
     * @return void
     *
     * @throws Exception
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
            $count = strlen($tokens[$tokenPosition + 1]->getValue());
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
     * @param int         $tokenPosition
     * @param list<Token> $tokens
     * @param int         $expected
     *
     * @return void
     *
     * @throws Exception
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
            $count = strlen($tokens[$tokenPosition - 1]->getValue());
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
