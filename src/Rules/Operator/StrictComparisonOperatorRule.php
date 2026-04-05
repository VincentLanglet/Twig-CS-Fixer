<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that strict comparison operators are used instead of "same as" and "not same as".
 */
final class StrictComparisonOperatorRule extends AbstractFixableRule
{
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        if (!StubbedEnvironment::satisfiesTwigVersion(3, 23)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::OPERATOR_TYPE, ['is', 'is not'])) {
            return;
        }

        $sameIndex = $tokens->findNext(Token::EMPTY_TOKENS, $tokenIndex + 1, exclude: true);
        Assert::notFalse($sameIndex, 'An OPERATOR_TYPE cannot be the last non-empty token');

        if (!$tokens->get($sameIndex)->isMatching(Token::TEST_NAME_TYPE, 'same')) {
            return;
        }

        $asIndex = $tokens->findNext(Token::EMPTY_TOKENS, $sameIndex + 1, exclude: true);
        Assert::notFalse($asIndex, 'A TEST_NAME_TYPE cannot be the last non-empty token');

        if (!$tokens->get($asIndex)->isMatching(Token::TEST_NAME_TYPE, 'as')) {
            return;
        }

        $nextIndex = $tokens->findNext(Token::EMPTY_TOKENS, $asIndex + 1, exclude: true);
        Assert::notFalse($nextIndex, 'A TEST_NAME_TYPE cannot be the last non-empty token');

        $isNegated = 'is not' === $token->getValue();
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

        $fixer->replaceToken($tokenIndex, $operator);
        for ($i = $tokenIndex + 1; $i <= $asIndex; ++$i) {
            $fixer->replaceToken($i, '');
        }

        $nextToken = $tokens->get($nextIndex);
        if ($nextToken->isMatching(Token::PUNCTUATION_TYPE, '(')) {
            $relatedToken = $nextToken->getRelatedToken();
            Assert::notNull($relatedToken, 'An opener is always related to an closer.');

            $fixer->replaceToken($nextIndex, '');
            $fixer->replaceToken($tokens->getIndex($relatedToken), '');
        }

        $fixer->endChangeSet();
    }
}
