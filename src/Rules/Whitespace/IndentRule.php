<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Whitespace;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;

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

    protected function process(int $tokenPosition, array $tokens): void
    {
        if ($this->useTab) {
            $this->spaceToTab($tokenPosition, $tokens);
        } else {
            $this->tabToSpace($tokenPosition, $tokens);
        }
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function tabToSpace(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::TAB_TOKENS)) {
            return;
        }

        $fixer = $this->addFixableError('A file must not be indented with tabs.', $token);
        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken(
            $tokenPosition,
            str_replace("\t", str_repeat(' ', $this->spaceRatio), $token->getValue())
        );
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function spaceToTab(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (1 !== $token->getPosition()) {
            return;
        }

        if (!$this->isTokenMatching($token, Token::WHITESPACE_TOKENS)) {
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
            $tokenPosition,
            str_replace(str_repeat(' ', $this->spaceRatio), "\t", $token->getValue())
        );
    }
}
