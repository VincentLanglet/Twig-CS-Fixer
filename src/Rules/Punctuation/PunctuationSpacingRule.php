<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Punctuation;

use TwigCsFixer\Rules\AbstractSpacingRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures there is no space before and after a punctuation except for ':' and ','.
 */
final class PunctuationSpacingRule extends AbstractSpacingRule implements ConfigurableRuleInterface
{
    private const DEFAULT_SPACE_BEFORE = [
        ')' => 0,
        ']' => 0,
        '}' => 0,
        ':' => 0,
        ',' => 0,
        '?:' => 0,
    ];
    private const DEFAULT_SPACE_AFTER = [
        '(' => 0,
        '[' => 0,
        '{' => 0,
        ':' => 1,
        ',' => 1,
        '?:' => 1,
    ];

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
        if (!$token->isMatching(Token::PUNCTUATION_TYPE)) {
            return null;
        }

        $value = $token->getValue();
        if (\array_key_exists($value, $this->beforeOverride)) {
            return $this->beforeOverride[$value];
        }

        return self::DEFAULT_SPACE_BEFORE[$value] ?? null;
    }

    protected function getSpaceAfter(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::PUNCTUATION_TYPE)) {
            return null;
        }

        $nextIndex = $tokens->findNext(Token::WHITESPACE_TOKENS, $tokenIndex + 1, exclude: true);
        Assert::notFalse($nextIndex, 'A PUNCTUATION_TYPE cannot be the last non-empty token');

        // We cannot change spaces after a token, if the next one has a constraint: `[1,2,3,]`.
        if (null !== $this->getSpaceBefore($nextIndex, $tokens)) {
            return null;
        }

        $value = $token->getValue();
        if (\array_key_exists($value, $this->afterOverride)) {
            return $this->afterOverride[$value];
        }

        return self::DEFAULT_SPACE_AFTER[$value] ?? null;
    }
}
