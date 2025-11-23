<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that the name is set at the end of the macro.
 */
final class EndMacroNameRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        // end block?
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::BLOCK_NAME_TYPE, 'endmacro')) {
            return;
        }

        // check if name is already set
        $nameToken = $this->findNameToken($tokenIndex + 1, $tokens, Token::NAME_TYPE);
        if ($nameToken instanceof Token) {
            return;
        }

        // find start block
        $indent = 0;
        $index = $tokenIndex - 1;
        while ($tokens->has($index)) {
            $current = $tokens->get($index);
            if ($current->isMatching(Token::BLOCK_NAME_TYPE, 'endmacro')) {
                ++$indent;
                --$index;
                continue;
            }

            if ($current->isMatching(Token::BLOCK_NAME_TYPE, 'macro')) {
                if ($indent > 0) {
                    --$indent;
                } elseif (0 === $indent) {
                    break;
                }
            }
            --$index;
        }

        // find name
        $nameToken = $this->findNameToken($index + 1, $tokens, Token::MACRO_NAME_TYPE);
        if (!$nameToken instanceof Token) {
            return;
        }
        $value = $nameToken->getValue();
        $fixer = $this->addFixableError(
            'The end macro must have the "'.$value.'" name.',
            $token
        );
        if (null === $fixer) {
            return;
        }
        $fixer->addContent($tokenIndex, ' '.$value);
    }

    private function findNameToken(int $index, Tokens $tokens, string|int $type): ?Token
    {
        while ($tokens->has($index) && $tokens->get($index)->isMatching(Token::WHITESPACE_TOKENS)) {
            ++$index;
        }
        if (!$tokens->has($index)) {
            return null;
        }
        $token = $tokens->get($index);

        return $token->isMatching($type) ? $token : null;
    }
}
