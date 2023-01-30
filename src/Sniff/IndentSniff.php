<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure that files are not indented with tabs.
 */
final class IndentSniff extends AbstractSniff implements ConfigurableSniffInterface
{
    public function __construct(private int $spaceRatio = 4)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'space_ratio' => $this->spaceRatio,
        ];
    }

    protected function process(int $tokenPosition, array $tokens): void
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
}
