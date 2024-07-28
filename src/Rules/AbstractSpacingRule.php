<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is one space before or after some tokens.
 */
abstract class AbstractSpacingRule extends AbstractFixableRule
{
    protected bool $skipIfNewLine = true;

    final protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $spaceAfter = $this->getSpaceAfter($tokenIndex, $tokens);
        $spaceBefore = $this->getSpaceBefore($tokenIndex, $tokens);

        if (null !== $spaceAfter) {
            $this->checkSpaceAfter($tokenIndex, $tokens, $spaceAfter);
        }

        if (null !== $spaceBefore) {
            $this->checkSpaceBefore($tokenIndex, $tokens, $spaceBefore);
        }
    }

    abstract protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int;

    abstract protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int;

    private function checkSpaceAfter(int $tokenIndex, Tokens $tokens, int $expected): void
    {
        $token = $tokens->get($tokenIndex);
        $next = $tokens->findNext(Token::INDENT_TOKENS, $tokenIndex + 1, exclude: true);
        if (false === $next) {
            return;
        }

        if ($tokens->get($next)->isMatching(Token::EOL_TOKENS)) {
            if ($this->skipIfNewLine) {
                return;
            }

            $found = 'newline';
        } elseif ($tokens->get($tokenIndex + 1)->isMatching(Token::WHITESPACE_TOKENS)) {
            $found = \strlen($tokens->get($tokenIndex + 1)->getValue());
        } else {
            $found = 0;
        }

        if ($expected === $found) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('Expecting %d whitespace after "%s"; found %s.', $expected, $token->getValue(), $found),
            $token,
            'After'
        );

        if (null === $fixer) {
            return;
        }

        $index = $tokenIndex + 1;
        $tokensToReplace = $this->skipIfNewLine
            ? Token::INDENT_TOKENS
            : Token::INDENT_TOKENS + Token::EOL_TOKENS;

        $fixer->beginChangeSet();
        while (
            $tokens->has($index)
            && $tokens->get($index)->isMatching($tokensToReplace)
        ) {
            $fixer->replaceToken($index, '');
            ++$index;
        }
        $fixer->addContent($tokenIndex, str_repeat(' ', $expected));
        $fixer->endChangeSet();
    }

    private function checkSpaceBefore(int $tokenIndex, Tokens $tokens, int $expected): void
    {
        $token = $tokens->get($tokenIndex);

        $previous = $tokens->findPrevious(Token::INDENT_TOKENS, $tokenIndex - 1, exclude: true);
        if (false === $previous) {
            return;
        }

        if ($tokens->get($previous)->isMatching(Token::EOL_TOKENS)) {
            if ($this->skipIfNewLine) {
                return;
            }

            $found = 'newline';
        } elseif ($tokens->get($tokenIndex - 1)->isMatching(Token::WHITESPACE_TOKENS)) {
            $found = \strlen($tokens->get($tokenIndex - 1)->getValue());
        } else {
            $found = 0;
        }

        if ($expected === $found) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('Expecting %d whitespace before "%s"; found %s.', $expected, $token->getValue(), $found),
            $token,
            'Before'
        );

        if (null === $fixer) {
            return;
        }

        $index = $tokenIndex - 1;
        $tokensToReplace = $this->skipIfNewLine
            ? Token::INDENT_TOKENS
            : Token::INDENT_TOKENS + Token::EOL_TOKENS;

        $fixer->beginChangeSet();
        while (
            $tokens->has($index)
            && $tokens->get($index)->isMatching($tokensToReplace)
        ) {
            $fixer->replaceToken($index, '');
            --$index;
        }
        $fixer->addContentBefore($tokenIndex, str_repeat(' ', $expected));
        $fixer->endChangeSet();
    }
}
