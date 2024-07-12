<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;

/**
 * Ensures that files end with one blank line.
 */
final class BlankEOFRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$token->isMatching(Token::EOF_TYPE)) {
            return;
        }

        $previous = $this->findPrevious(Token::EOL_TYPE, $tokens, $tokenPosition - 1, true);
        if (false === $previous) {
            // If all previous tokens are EOL_TYPE, we have to count one more
            // since $tokenPosition start at 0
            $i = $tokenPosition + 1;
        } else {
            $i = $tokenPosition - $previous - 1;
        }

        // Only 0 or 2+ blank lines are reported.
        if (1 === $i) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('A file must end with 1 blank line; found %d', $i),
            $token
        );

        if (null === $fixer) {
            return;
        }

        // Because we added manually extra empty lines to the count
        $i = min($i, $tokenPosition);

        if (0 === $i) {
            $fixer->addNewlineBefore($tokenPosition);
        } else {
            $fixer->beginChangeSet();
            while ($i >= 2 || $i === $tokenPosition) {
                $fixer->replaceToken($tokenPosition - $i, '');
                --$i;
            }
            $fixer->endChangeSet();
        }
    }
}
