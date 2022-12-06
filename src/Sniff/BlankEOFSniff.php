<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure that files ends with one blank line.
 */
final class BlankEOFSniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::EOF_TYPE)) {
            return;
        }

        $i = 0;
        while (
            isset($tokens[$tokenPosition - ($i + 1)])
            && $this->isTokenMatching($tokens[$tokenPosition - ($i + 1)], Token::EOL_TYPE)
        ) {
            $i++;
        }

        if ($tokenPosition === $i) {
            // If all previous tokens are EOL_TYPE, we have to count one more
            // since there is no EOL token used for the previous non-empty line
            $i++;
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
                $i--;
            }
            $fixer->endChangeSet();
        }
    }
}
