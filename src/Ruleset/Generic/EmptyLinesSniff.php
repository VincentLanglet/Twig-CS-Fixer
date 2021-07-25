<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset\Generic;

use Exception;
use TwigCsFixer\Sniff\AbstractSniff;
use TwigCsFixer\Token\Token;

/**
 * Checks that there are not 2 empty lines following each other.
 */
class EmptyLinesSniff extends AbstractSniff
{
    /**
     * @param int               $tokenPosition
     * @param array<int, Token> $tokens
     *
     * @return void
     *
     * @throws Exception
     */
    public function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        if ($this->isTokenMatching($token, Token::EOL_TYPE)) {
            $i = 0;
            while (
                isset($tokens[$tokenPosition - ($i + 1)])
                && $this->isTokenMatching($tokens[$tokenPosition - ($i + 1)], Token::EOL_TYPE)
            ) {
                $i++;
            }

            if (1 < $i) {
                $fix = $this->addFixableError(
                    sprintf('More than 1 empty lines are not allowed, found %d', $i),
                    $token
                );

                if ($fix) {
                    \assert(null !== $this->fixer);

                    $this->fixer->beginChangeset();
                    while ($i > 1) {
                        $this->fixer->replaceToken($tokenPosition - $i, '');
                        $i--;
                    }
                    $this->fixer->endChangeset();
                }
            }
        }
    }
}
