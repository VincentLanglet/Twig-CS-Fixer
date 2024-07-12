<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Punctuation;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
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

    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$token->isMatching(Token::PUNCTUATION_TYPE, [')', '}', ']'])) {
            return;
        }

        $previousPosition = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previousPosition, 'A closer cannot be the first token.');

        if ($tokens[$previousPosition]->getLine() === $token->getLine()) {
            // The closer is on the same line as the last element.
            return;
        }

        $isMatchingComma = $tokens[$previousPosition]->isMatching(Token::PUNCTUATION_TYPE, ',');
        if ($this->useTrailingComma === $isMatchingComma) {
            return;
        }

        $fixer = $this->addFixableError(
            $this->useTrailingComma
                ? 'Multi-line arrays, objects and parameters lists should have trailing comma.'
                : 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
            $token
        );

        if (null === $fixer) {
            return;
        }

        if ($this->useTrailingComma) {
            $fixer->addContent($previousPosition, ',');
        } else {
            $fixer->replaceToken($previousPosition, '');
        }
    }
}
