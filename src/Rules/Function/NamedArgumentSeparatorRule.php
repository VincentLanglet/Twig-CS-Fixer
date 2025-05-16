<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Function;

use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures named arguments use `:` syntax instead of `=` (For `twig/twig >= 3.12.0`).
 */
final class NamedArgumentSeparatorRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        if (!StubbedEnvironment::satisfiesTwigVersion(3, 12)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::NAMED_ARGUMENT_SEPARATOR_TYPE)) {
            return;
        }

        if (':' === $token->getValue()) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('Named arguments should be declared with the separator "%s".', ':'),
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($tokenIndex, ':');
    }
}
