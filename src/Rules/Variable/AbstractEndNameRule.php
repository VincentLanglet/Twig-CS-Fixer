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

        $indent = 1;
        $index = $tokenIndex;
        while ($indent > 0) {
            $previous = $tokens->findPrevious(Token::BLOCK_NAME_TYPE, $index - 1);
            if (false !== $previous) {
                if ($tokens->get($previous)->getValue() === $this->getEndName()) {
                    ++$indent;
                } elseif ($tokens->get($previous)->getValue() === $this->getStartName()) {
                    --$indent;
                }
                $index = $previous;
            }
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
        $next = $tokens->findNext([Token::NAME_TYPE, Token::MACRO_NAME_TYPE, Token::BLOCK_END_TYPE], $index + 1);
        if (false === $next) {
            return null;
        }
        $token = $tokens->get($next);

        return $token->isMatching(Token::BLOCK_END_TYPE) ? null : $token;
    }
}
