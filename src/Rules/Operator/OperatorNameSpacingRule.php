<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is no consecutive spaces inside operator names.
 */
final class OperatorNameSpacingRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::OPERATOR_TYPE)) {
            return;
        }

        $value = $token->getValue();
        // Ignore multi lines operators
        if (1 === preg_match('/\r\n?|\n/', $value)) {
            return;
        }

        $newValue = preg_replace('#\s+#', ' ', $value);
        if (!\is_string($newValue) || $value === $newValue) {
            return;
        }

        $fixer = $this->addFixableError(
            'A single line operator should not have consecutive spaces.',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($tokenIndex, $newValue);
    }
}
