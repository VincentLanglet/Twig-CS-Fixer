<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that files end with one blank line.
 */
final class BlankEOFRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::EOF_TYPE)) {
            return;
        }

        $previous = $tokens->findPrevious(Token::EOL_TYPE, $tokenIndex - 1, exclude: true);
        if (false === $previous) {
            // If all previous tokens are EOL_TYPE, we have to count one more
            // since $tokenIndex start at 0
            $i = $tokenIndex + 1;
        } else {
            $i = $tokenIndex - $previous - 1;
        }

        // Only 0 or 2+ blank lines are reported.
        if (1 === $i) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('A file must end with 1 blank line; found %d', $i),
            $token
        );

        if (null === $fixer) {
            return;
        }

        // Because we added manually extra empty lines to the count
        $i = min($i, $tokenIndex);

        if (0 === $i) {
            $fixer->addNewlineBefore($tokenIndex);
        } else {
            $fixer->beginChangeSet();
            while ($i >= 2 || $i === $tokenIndex) {
                $fixer->replaceToken($tokenIndex - $i, '');
                --$i;
            }
            $fixer->endChangeSet();
        }
    }
}
