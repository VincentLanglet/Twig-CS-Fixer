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

        $sameIndex = $tokens->findNext(Token::EMPTY_TOKENS, $tokenIndex + 1, exclude: true);
        if (false === $sameIndex) {
            return;
        }

        if (!$tokens->get($sameIndex)->isMatching(Token::TEST_NAME_TYPE, 'same')) {
            return;
        }

        $asIndex = $tokens->findNext(Token::EMPTY_TOKENS, $sameIndex + 1, exclude: true);
        if (false === $asIndex) {
            return;
        }

        if (!$tokens->get($asIndex)->isMatching(Token::TEST_NAME_TYPE, 'as')) {
            return;
        }

        $nextIndex = $tokens->findNext(Token::EMPTY_TOKENS, $asIndex + 1, exclude: true);
        if (false === $nextIndex) {
            return;
        }

        $operator = $isNegated ? '!==' : '===';
        $oldOperator = $isNegated ? 'not same as' : 'same as';

        $fixer = $this->addFixableError(
            \sprintf('Use strict comparison operator "%s" instead of "%s".', $operator, $oldOperator),
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->beginChangeSet();

        $nextToken = $tokens->get($nextIndex);

        if ($nextToken->isMatching(Token::PUNCTUATION_TYPE, '(')) {
            $relatedToken = $nextToken->getRelatedToken();
            if (null === $relatedToken) {
                $fixer->endChangeSet();

                return;
            }

            $closeParenthesisIndex = $tokens->getIndex($relatedToken);

            $fixer->replaceToken($tokenIndex, $operator);

            for ($i = $tokenIndex + 1; $i <= $nextIndex; ++$i) {
                $fixer->replaceToken($i, '');
            }

            $fixer->replaceToken($closeParenthesisIndex, '');
        } else {
            $fixer->replaceToken($tokenIndex, $operator);

            for ($i = $tokenIndex + 1; $i <= $asIndex; ++$i) {
                $fixer->replaceToken($i, '');
            }
        }

        $fixer->endChangeSet();
    }
}
