<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Punctuation;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that single-line arrays, objects and argument lists do not have a trailing comma.
 */
final class TrailingCommaSingleLineRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, Tokens $tokens): void
    {
        $token = $tokens->get($tokenPosition);
        if (!$token->isMatching(Token::PUNCTUATION_TYPE, [')', '}', ']'])) {
            return;
        }

        $previousPosition = $tokens->findPrevious(Token::EMPTY_TOKENS, $tokenPosition - 1, exclude: true);
        Assert::notFalse($previousPosition, 'A closer cannot be the first token.');

        if ($tokens->get($previousPosition)->getLine() !== $token->getLine()) {
            // The closer is on a different line than the last element.
            return;
        }

        if (!$tokens->get($previousPosition)->isMatching(Token::PUNCTUATION_TYPE, ',')) {
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
