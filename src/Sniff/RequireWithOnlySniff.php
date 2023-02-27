<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensure includes that pass additional variables always use "with ... only".
 */
final class RequireWithOnlySniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        // If {% is found
        if (!$this->isTokenMatching($token, Token::BLOCK_START_TYPE)) {
            return;
        }

        // Find the next %}
        $endTokenPosition = $this->findNext(Token::BLOCK_END_TYPE, $tokens, $tokenPosition);
        Assert::notFalse($endTokenPosition, 'An open block must have a closer.');
        $endToken = $tokens[$endTokenPosition];

        // Check block is related to include
        $includeTokenPosition = $this->findNextWithValue(Token::BLOCK_TAG_TYPE, $tokens, $tokenPosition, 'include');
        if (false === $includeTokenPosition || $includeTokenPosition > $endTokenPosition) {
            return;
        }

        // Check if there is a "with"
        $withTokenPosition = $this->findNextWithValue(Token::NAME_TYPE, $tokens, $tokenPosition, 'with');
        if (false === $withTokenPosition || $withTokenPosition > $endTokenPosition) {
            return;
        }

        // Check if the last NAME token is "only"
        $previousNameTokenPosition = $this->findPreviousWithValue(Token::NAME_TYPE, $tokens, $endTokenPosition, 'only');
        if ($previousNameTokenPosition && $previousNameTokenPosition > $tokenPosition) {
            return;
        }

        $fixer = $this->addFixableError(
            'Includes passing additional variables must always use "with ... only".',
            $endToken
        );

        if (null === $fixer) {
            return;
        }

        $fixer->addContentBefore($endTokenPosition, 'only ');
    }

    private function findNextWithValue(int $type, array $tokens, int $tokenPosition, string $value): false|int
    {
        $position = $this->findNext($type, $tokens, $tokenPosition);
        if (!$this->tokenHasValue($position, $tokens, $value)) {
            return false;
        }

        return $position;
    }

    private function findPreviousWithValue(int $type, array $tokens, int $tokenPosition, string $value): false|int
    {
        $position = $this->findPrevious($type, $tokens, $tokenPosition);
        if (!$this->tokenHasValue($position, $tokens, $value)) {
            return false;
        }

        return $position;
    }

    private function tokenHasValue(bool|int $position, array $tokens, string $value): false|int
    {
        if (false === $position || !isset($tokens[$position])) {
            return false;
        }
        $token = $tokens[$position];

        if ($token->getValue() !== $value) {
            return false;
        }

        return $position;
    }
}
