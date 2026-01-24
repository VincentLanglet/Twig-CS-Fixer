<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures there is no space before and after ':', '..' and '?.', and
 * there is one space before and after other operators.
 */
final class OperatorSpacingRule extends AbstractSpacingRule implements ConfigurableRuleInterface
{
    /**
     * @param array<string, int|null> $beforeOverride
     * @param array<string, int|null> $afterOverride
     */
    public function __construct(
        private array $beforeOverride = [],
        private array $afterOverride = [],
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'before' => $this->beforeOverride,
            'after' => $this->afterOverride,
        ];
    }

    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::OPERATOR_TYPE)) {
            return null;
        }

        $value = $token->getValue();
        if (\array_key_exists($value, $this->beforeOverride)) {
            return $this->beforeOverride[$value];
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ['not', '-', '+'])) {
            return $this->isUnary($tokenIndex, $tokens) ? null : 1;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ['..', '?.'])) {
            return 0;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ':')) {
            $relatedToken = $token->getRelatedToken();

            return null !== $relatedToken && '?' === $relatedToken->getValue() ? 1 : 0;
        }

        return 1;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::OPERATOR_TYPE)) {
            return null;
        }

        $value = $token->getValue();
        if (\array_key_exists($value, $this->afterOverride)) {
            return $this->afterOverride[$value];
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ['-', '+'])) {
            return $this->isUnary($tokenIndex, $tokens) ? 0 : 1;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ['..', '?.'])) {
            return 0;
        }

        if ($token->isMatching(Token::OPERATOR_TYPE, ':')) {
            $relatedToken = $token->getRelatedToken();

            return null !== $relatedToken && '?' === $relatedToken->getValue() ? 1 : 0;
        }

        return 1;
    }

    private function isUnary(int $tokenIndex, Tokens $tokens): bool
    {
        $previous = $tokens->findPrevious(Token::EMPTY_TOKENS, $tokenIndex - 1, exclude: true);
        Assert::notFalse($previous, 'An OPERATOR_TYPE cannot be the first non-empty token');

        $previousToken = $tokens->get($previous);

        return $previousToken->isMatching([
            // {{ 1 * -2 }}
            Token::OPERATOR_TYPE,
            // {{ -2 }}
            Token::VAR_START_TYPE,
            // {% if -2 ... %}
            Token::BLOCK_NAME_TYPE,
        ])
        // {{ 1 + (-2) }}
        || $previousToken->isMatching(Token::PUNCTUATION_TYPE, ['(', '[', ':', ',']);
    }
}
