<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that 2 empty lines do not follow each other.
 */
final class EmptyLinesRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::EOL_TYPE)) {
            return;
        }

        if ($tokens->get($tokenIndex + 1)->isMatching(Token::EOL_TYPE)) {
            // Rely on the next token check instead to avoid duplicate errors
            return;
        }

        $previous = $tokens->findPrevious(Token::EOL_TYPE, $tokenIndex, exclude: true);
        if (false === $previous) {
            // If all previous tokens are EOL_TYPE, we have to count one more
            // since $tokenIndex start at 0
            $i = $tokenIndex + 1;
        } else {
            $i = $tokenIndex - $previous - 1;
        }

        if ($i < 2) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('More than 1 empty line is not allowed, found %d', $i),
            $token
        );

        if (null === $fixer) {
            return;
        }

        // Because we added manually extra empty lines to the count
        $i = min($i, $tokenIndex);

        $fixer->beginChangeSet();
        while ($i >= 2 || $i === $tokenIndex) {
            $fixer->replaceToken($tokenIndex - $i, '');
            --$i;
        }
        $fixer->endChangeSet();
    }
}
