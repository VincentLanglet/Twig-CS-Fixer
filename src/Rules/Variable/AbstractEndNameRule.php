<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that the name is set at the end.
 */
abstract class AbstractEndNameRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::BLOCK_NAME_TYPE, $this->getEndName())) {
            return;
        }

        $nameToken = $this->findNameToken($tokenIndex + 1, $tokens);
        if ($nameToken instanceof Token) {
            return;
        }

        $indent = 0;
        $index = $tokenIndex - 1;
        while ($tokens->has($index)) {
            $current = $tokens->get($index);
            if ($current->isMatching(Token::BLOCK_NAME_TYPE, $this->getEndName())) {
                ++$indent;
                --$index;
                continue;
            }

            if ($current->isMatching(Token::BLOCK_NAME_TYPE, $this->getStartName())) {
                if ($indent > 0) {
                    --$indent;
                } elseif (0 === $indent) {
                    break;
                }
            }
            --$index;
        }

        $nameToken = $this->findNameToken($index + 1, $tokens);
        if (!$nameToken instanceof Token) {
            return;
        }
        $value = $nameToken->getValue();
        $fixer = $this->addFixableError(
            'The '.$this->getEndName().' must have the "'.$value.'" name.',
            $token
        );
        if (null === $fixer) {
            return;
        }
        $fixer->addContent($tokenIndex, ' '.$value);
    }

    /**
     * Gets the end name.
     */
    abstract protected function getEndName(): string;

    /**
     * Gets the start name.
     */
    abstract protected function getStartName(): string;

    private function findNameToken(int $index, Tokens $tokens): ?Token
    {
        while ($tokens->has($index) && $tokens->get($index)->isMatching(Token::WHITESPACE_TOKENS)) {
            ++$index;
        }
        if (!$tokens->has($index)) {
            return null;
        }
        $token = $tokens->get($index);

        return $token->isMatching([Token::NAME_TYPE, Token::MACRO_NAME_TYPE]) ? $token : null;
    }
}
