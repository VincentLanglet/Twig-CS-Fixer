<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Token\Token;

/**
 * Ensures there is one space before or after some tokens.
 */
abstract class AbstractSpacingRule extends AbstractFixableRule
{
    protected bool $skipIfNewLine = true;

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

        $next = $this->findNext(Token::INDENT_TOKENS, $tokens, $tokenPosition + 1, true);
        if (false === $next) {
            return;
        }

        if ($tokens[$next]->isMatching(Token::EOL_TOKENS)) {
            if ($this->skipIfNewLine) {
                return;
            }

            $found = 'newline';
        } elseif ($tokens[$tokenPosition + 1]->isMatching(Token::WHITESPACE_TOKENS)) {
            $found = \strlen($tokens[$tokenPosition + 1]->getValue());
        } else {
            $found = 0;
        }

        if ($expected === $found) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('Expecting %d whitespace after "%s"; found %s.', $expected, $token->getValue(), $found),
            $token,
            'After'
        );

        if (null === $fixer) {
            return;
        }

        $index = $tokenPosition + 1;
        $tokensToReplace = $this->skipIfNewLine
            ? Token::INDENT_TOKENS
            : Token::INDENT_TOKENS + Token::EOL_TOKENS;

        $fixer->beginChangeSet();
        while (
            isset($tokens[$index])
            && $tokens[$index]->isMatching($tokensToReplace)
        ) {
            $fixer->replaceToken($index, '');
            ++$index;
        }
        $fixer->addContent($tokenPosition, str_repeat(' ', $expected));
        $fixer->endChangeSet();
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function checkSpaceBefore(int $tokenPosition, array $tokens, int $expected): void
    {
        $token = $tokens[$tokenPosition];

        $previous = $this->findPrevious(Token::INDENT_TOKENS, $tokens, $tokenPosition - 1, true);
        if (false === $previous) {
            return;
        }

        if ($tokens[$previous]->isMatching(Token::EOL_TOKENS)) {
            if ($this->skipIfNewLine) {
                return;
            }

            $found = 'newline';
        } elseif ($tokens[$tokenPosition - 1]->isMatching(Token::WHITESPACE_TOKENS)) {
            $found = \strlen($tokens[$tokenPosition - 1]->getValue());
        } else {
            $found = 0;
        }

        if ($expected === $found) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('Expecting %d whitespace before "%s"; found %s.', $expected, $token->getValue(), $found),
            $token,
            'Before'
        );

        if (null === $fixer) {
            return;
        }

        $index = $tokenPosition - 1;
        $tokensToReplace = $this->skipIfNewLine
            ? Token::INDENT_TOKENS
            : Token::INDENT_TOKENS + Token::EOL_TOKENS;

        $fixer->beginChangeSet();
        while (
            isset($tokens[$index])
            && $tokens[$index]->isMatching($tokensToReplace)
        ) {
            $fixer->replaceToken($index, '');
            --$index;
        }
        $fixer->addContentBefore($tokenPosition, str_repeat(' ', $expected));
        $fixer->endChangeSet();
    }
}
