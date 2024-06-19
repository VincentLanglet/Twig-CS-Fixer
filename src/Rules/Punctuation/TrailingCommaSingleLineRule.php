<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Punctuation;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensures that single-line arrays, objects and argument lists do not have a trailing comma.
 */
final class TrailingCommaSingleLineRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [')', '}', ']'])) {
            return;
        }

        $previousPosition = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previousPosition, 'A closer cannot be the first token.');

        if ($tokens[$previousPosition]->getLine() !== $token->getLine()) {
            // The closer is on a different line than the last element.
            return;
        }

        if (!$this->isTokenMatching($tokens[$previousPosition], Token::PUNCTUATION_TYPE, ',')) {
            return;
        }

        $fixer = $this->addFixableError(
            'Single-line arrays, objects and parameters lists should not have trailing comma.',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($previousPosition, '');
    }
}
