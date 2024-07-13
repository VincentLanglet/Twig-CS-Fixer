<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that 2 empty lines do not follow each other.
 */
final class EmptyLinesRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, Tokens $tokens): void
    {
        $token = $tokens->get($tokenPosition);
        if (!$token->isMatching(Token::EOL_TYPE)) {
            return;
        }

        if ($tokens->get($tokenPosition + 1)->isMatching(Token::EOL_TYPE)) {
            // Rely on the next token check instead to avoid duplicate errors
            return;
        }

        $previous = $tokens->findPrevious(Token::EOL_TYPE, $tokenPosition, exclude: true);
        if (false === $previous) {
            // If all previous tokens are EOL_TYPE, we have to count one more
            // since $tokenPosition start at 0
            $i = $tokenPosition + 1;
        } else {
            $i = $tokenPosition - $previous - 1;
        }

        if ($i < 2) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('More than 1 empty line is not allowed, found %d', $i),
            $token
        );

        if (null === $fixer) {
            return;
        }

        // Because we added manually extra empty lines to the count
        $i = min($i, $tokenPosition);

        $fixer->beginChangeSet();
        while ($i >= 2 || $i === $tokenPosition) {
            $fixer->replaceToken($tokenPosition - $i, '');
            --$i;
        }
        $fixer->endChangeSet();
    }
}
