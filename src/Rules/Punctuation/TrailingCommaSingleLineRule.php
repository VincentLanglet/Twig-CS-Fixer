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

        $relatedToken = $token->getRelatedToken();
        Assert::notNull($relatedToken, 'A closer must have a related token.');

        if ($relatedToken->getLine() !== $token->getLine()) {
            // Multiline.
            return;
        }

        $previousPosition = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previousPosition, 'A closer cannot be the first token.');

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
