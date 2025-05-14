<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Literal;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that strings use single quotes when possible.
 */
final class SingleQuoteRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    public function __construct(private bool $skipStringContainingSingleQuote = true)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'skipStringContainingSingleQuote' => $this->skipStringContainingSingleQuote,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::STRING_TYPE)) {
            return;
        }

        $content = $token->getValue();
        if (
            !str_starts_with($content, '"')
            || str_contains($content, '\'') && $this->skipStringContainingSingleQuote
        ) {
            return;
        }

        $fixer = $this->addFixableError('String should be declared with single quotes.', $token);
        if (null === $fixer) {
            return;
        }

        $content = substr($content, 1, -1);
        $content = str_replace(
            ['\\"', '\\#{', '#\\{', '\\\'', '\''],
            ['"', '#{', '#{', '\'', '\\\''],
            $content
        );
        $fixer->replaceToken($tokenIndex, '\''.$content.'\'');
    }
}
