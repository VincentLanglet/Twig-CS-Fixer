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
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::PUNCTUATION_TYPE, [')', '}', ']'])) {
            return;
        }

        $previous = $tokens->findPrevious(Token::EMPTY_TOKENS, $tokenIndex - 1, exclude: true);
        Assert::notFalse($previous, 'A closer cannot be the first token.');

        if (false !== $tokens->findNext(Token::EOL_TYPE, $previous, $tokenIndex)) {
            // The closer is on a different line than the last element.
            return;
        }

        if (!$tokens->get($previous)->isMatching(Token::PUNCTUATION_TYPE, ',')) {
            return;
        }

        $fixer = $this->addFixableError(
            'Single-line arrays, objects and parameters lists should not have trailing comma.',
            $tokens->get($previous)
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($previous, '');
    }
}
