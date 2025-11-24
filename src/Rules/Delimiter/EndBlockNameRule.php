<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Delimiter;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that end block or end macro has a name.
 */
class EndBlockNameRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    /**
     * @param list<string> $blocks
     */
    public function __construct(private array $blocks = ['block', 'macro'])
    {
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        foreach ($this->blocks as $block) {
            $this->processBlock($tokenIndex, $tokens, $block);
        }
    }

    public function getConfiguration(): array
    {
        return [
            'blocks' => $this->blocks,
        ];
    }

    private function processBlock(int $tokenIndex, Tokens $tokens, string $block): void
    {
        $endName = 'end'.$block;
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::BLOCK_NAME_TYPE, $endName)) {
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
                if ($tokens->get($previous)->getValue() === $endName) {
                    ++$indent;
                } elseif ($tokens->get($previous)->getValue() === $block) {
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
            'The '.$endName.' must have the "'.$value.'" name.',
            $token
        );
        if (null === $fixer) {
            return;
        }
        $fixer->addContent($tokenIndex, ' '.$value);
    }

    private function findNameToken(int $index, Tokens $tokens): ?Token
    {
        $next = $tokens->findNext([Token::NAME_TYPE, Token::MACRO_NAME_TYPE, Token::BLOCK_END_TYPE], $index);
        if (false === $next) {
            return null;
        }
        $token = $tokens->get($next);

        return $token->isMatching(Token::BLOCK_END_TYPE) ? null : $token;
    }
}
