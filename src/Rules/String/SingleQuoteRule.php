<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\String;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;

/**
 * Ensures that string use single quotes when possible.
 */
final class SingleQuoteRule extends AbstractFixableRule
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::STRING_TYPE)) {
            return;
        }

        $content = $token->getValue();
        if ('"' !== $content[0]) {
            return;
        }

        $fixer = $this->addFixableError('String should be defined with single quotes.', $token);
        if (null === $fixer) {
            return;
        }

        $content = substr($content, 1, -1);
        $content = str_replace(
            ['\\"', '\\#{', '#\\{', '\\\'', '\''],
            ['"', '#{', '#{', '\'', '\\\''],
            $content);
        $fixer->replaceToken($tokenPosition, '\''.$content.'\'');
    }
}
