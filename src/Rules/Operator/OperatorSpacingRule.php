<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Operator;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

/**
 * Ensures there is no space before and after ':', '..' and '?.', and
 * there is one space before and after other non-unary and non-ternary operators.
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

        if ($token->isMatching(Token::OPERATOR_TYPE, [':', '..', '?.'])) {
            return 0;
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

        if ($token->isMatching(Token::OPERATOR_TYPE, [':', '..', '?.'])) {
            return 0;
        }

        return 1;
    }
}
