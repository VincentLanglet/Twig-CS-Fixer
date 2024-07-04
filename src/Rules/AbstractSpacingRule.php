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

        if ($this->skipIfNewLine) {
            // Ignore new line
            $next = $this->findNext(Token::INDENT_TOKENS, $tokens, $tokenPosition + 1, true);
            if (false === $next || $this->isTokenMatching($tokens[$next], Token::EOL_TOKENS)) {
                return;
            }
        }

        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::WHITESPACE_TOKENS)) {
            $found = \strlen($tokens[$tokenPosition + 1]->getValue());
        } elseif ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::EOL_TOKENS)) {
            $found = 'newline';
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
            && $this->isTokenMatching($tokens[$index], $tokensToReplace)
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

        if ($this->skipIfNewLine) {
            // Ignore new line
            $previous = $this->findPrevious(Token::INDENT_TOKENS, $tokens, $tokenPosition - 1, true);
            if (false === $previous || $this->isTokenMatching($tokens[$previous], Token::EOL_TOKENS)) {
                return;
            }
        }

        if ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::WHITESPACE_TOKENS)) {
            $found = \strlen($tokens[$tokenPosition - 1]->getValue());
        } elseif ($this->isTokenMatching($tokens[$tokenPosition - 1], Token::EOL_TOKENS)) {
            $found = 'newline';
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
            && $this->isTokenMatching($tokens[$index], $tokensToReplace)
        ) {
            $fixer->replaceToken($index, '');
            --$index;
        }
        $fixer->addContentBefore($tokenPosition, str_repeat(' ', $expected));
        $fixer->endChangeSet();
    }
}
