<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Punctuation;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that multi-line arrays, objects and argument lists have a trailing comma.
 */
final class TrailingCommaMultiLineRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    public function __construct(private bool $useTrailingComma = true)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'comma' => $this->useTrailingComma,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if ($token->isMatching(Token::PUNCTUATION_TYPE, ')')) {
            $related = $token->getRelatedToken();
            Assert::notNull($related, 'A closer is always related to an opener.');

            $relatedIndex = $tokens->getIndex($related);
            $relatedPrevious = $tokens->findPrevious(Token::EMPTY_TOKENS, $relatedIndex - 1, exclude: true);
            Assert::notFalse($relatedPrevious, 'An opener cannot be the first token.');

            if (!$tokens->get($relatedPrevious)->isMatching([Token::FUNCTION_NAME_TYPE, Token::FILTER_NAME_TYPE])) {
                return;
            }
        } elseif (!$token->isMatching(Token::PUNCTUATION_TYPE, ['}', ']'])) {
            return;
        }

        $previous = $tokens->findPrevious(Token::EMPTY_TOKENS, $tokenIndex - 1, exclude: true);
        Assert::notFalse($previous, 'A closer cannot be the first token.');

        if (false === $tokens->findNext(Token::EOL_TYPE, $previous, $tokenIndex)) {
            // The closer is on the same line as the last element.
            return;
        }

        $previousToken = $tokens->get($previous);
        if ($previousToken === $token->getRelatedToken()) {
            // There is no element, so adding a trailing comma will break the code.
            return;
        }

        $isMatchingComma = $previousToken->isMatching(Token::PUNCTUATION_TYPE, ',');
        if ($this->useTrailingComma === $isMatchingComma) {
            return;
        }

        $fixer = $this->addFixableError(
            $this->useTrailingComma
                ? 'Multi-line arrays, objects and parameters lists should have trailing comma.'
                : 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
            $this->useTrailingComma
                ? $tokens->get($previous + 1)
                : $tokens->get($previous)
        );

        if (null === $fixer) {
            return;
        }

        if ($this->useTrailingComma) {
            $fixer->addContent($previous, ',');
        } else {
            $fixer->replaceToken($previous, '');
        }
    }
}
