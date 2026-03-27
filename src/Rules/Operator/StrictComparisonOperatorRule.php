<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures that strict comparison operators are used instead of "same as" and "not same as".
 */
final class StrictComparisonOperatorRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);

        if (!$token->isMatching(Token::OPERATOR_TYPE, ['is', 'is not'])) {
            return;
        }

        $isNegated = $token->isMatching(Token::OPERATOR_TYPE, 'is not');

        $sameAsIndex = $tokens->findNext(Token::WHITESPACE_TOKENS, $tokenIndex + 1, exclude: true);
        if (false === $sameAsIndex) {
            return;
        }

        $sameAsToken = $tokens->get($sameAsIndex);
        $targetIndex = $sameAsIndex;

        if (!$sameAsToken->isMatching(Token::TEST_NAME_TYPE, 'same')) {
            return;
        }

        $asIndex = $tokens->findNext(Token::WHITESPACE_TOKENS, $targetIndex + 1, exclude: true);
        if (false === $asIndex || !$tokens->get($asIndex)->isMatching(Token::TEST_NAME_TYPE, 'as')) {
            return;
        }

        $openParenthesisIndex = $tokens->findNext(Token::WHITESPACE_TOKENS, $asIndex + 1, exclude: true);
        if (false === $openParenthesisIndex || !$tokens->get($openParenthesisIndex)->isMatching(Token::PUNCTUATION_TYPE, '(')) {
            return;
        }

        $closeParenthesisIndex = $this->findMatchingParenthesis($tokens, $openParenthesisIndex);
        if (false === $closeParenthesisIndex) {
            return;
        }

        $fixer = $this->addFixableError(
            'Use strict comparison operators === / !== instead of same as / not same as.',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->beginChangeSet();

        $replacement = $isNegated ? '!==' : '===';

        $fixer->replaceToken($tokenIndex, $replacement);

        for ($i = $tokenIndex + 1; $i <= $asIndex; ++$i) {
            $fixer->replaceToken($i, '');
        }

        // We want only one whitespace between the operator and the parenthesis content.
        // If there is already a whitespace between "as" and "(", we can remove the "(".
        // Otherwise, we replace "(" by a whitespace.
        $hasWhitespaceBeforeParen = $tokens->get($openParenthesisIndex - 1)->isMatching(Token::WHITESPACE_TOKENS);
        $fixer->replaceToken($openParenthesisIndex, $hasWhitespaceBeforeParen ? '' : ' ');
        $fixer->replaceToken($closeParenthesisIndex, '');

        $fixer->endChangeSet();
    }

    private function findMatchingParenthesis(Tokens $tokens, int $openParenthesisIndex): int|false
    {
        $level = 0;
        $tokensArray = $tokens->toArray();
        $count = \count($tokensArray);

        for ($i = $openParenthesisIndex; $i < $count; ++$i) {
            $token = $tokensArray[$i];

            if ($token->isMatching(Token::PUNCTUATION_TYPE, '(')) {
                ++$level;

                continue;
            }

            if ($token->isMatching(Token::PUNCTUATION_TYPE, ')')) {
                --$level;

                if (0 === $level) {
                    return $i;
                }
            }
        }

        return false;
    }
}
