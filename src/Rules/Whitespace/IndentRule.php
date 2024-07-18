<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that files are indented with spaces (or tabs).
 */
final class IndentRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    public function __construct(
        private int $spaceRatio = 4,
        private bool $useTab = false,
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'spaceRatio' => $this->spaceRatio,
            'useTab' => $this->useTab,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        if ($this->useTab) {
            $this->spaceToTab($tokenIndex, $tokens);
        } else {
            $this->tabToSpace($tokenIndex, $tokens);
        }
    }

    private function tabToSpace(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::TAB_TOKENS)) {
            return;
        }

        $fixer = $this->addFixableError('A file must not be indented with tabs.', $token);
        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken(
            $tokenIndex,
            str_replace("\t", str_repeat(' ', $this->spaceRatio), $token->getValue())
        );
    }

    private function spaceToTab(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (1 !== $token->getLinePosition()) {
            return;
        }

        if (!$token->isMatching(Token::WHITESPACE_TOKENS)) {
            return;
        }

        if (\strlen($token->getValue()) < $this->spaceRatio) {
            return;
        }

        $fixer = $this->addFixableError('A file must not be indented with spaces.', $token);
        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken(
            $tokenIndex,
            str_replace(str_repeat(' ', $this->spaceRatio), "\t", $token->getValue())
        );
    }
}
