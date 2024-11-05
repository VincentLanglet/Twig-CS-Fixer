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
        '.' => 0,
        ',' => 0,
        '|' => 0,
        '?:' => 0,
    ];
    private const DEFAULT_SPACE_AFTER = [
        '(' => 0,
        '[' => 0,
        '{' => 0,
        '.' => 0,
        '|' => 0,
        ':' => 1,
        ',' => 1,
        '?:' => 1,
    ];

    /** @var array<string, int|null> */
    private array $punctuationWithSpaceBefore;

    /** @var array<string, int|null> */
    private array $punctuationWithSpaceAfter;

    /**
     * @param array<string, int|null> $punctuationWithSpaceBefore
     * @param array<string, int|null> $punctuationWithSpaceAfter
     */
    public function __construct(
        array $punctuationWithSpaceBefore = [],
        array $punctuationWithSpaceAfter = [],
    ) {
        $this->punctuationWithSpaceBefore = $punctuationWithSpaceBefore + self::DEFAULT_SPACE_BEFORE;
        $this->punctuationWithSpaceAfter = $punctuationWithSpaceAfter + self::DEFAULT_SPACE_AFTER;
    }

    public function getConfiguration(): array
    {
        return [
            'before' => $this->punctuationWithSpaceBefore,
            'after' => $this->punctuationWithSpaceAfter,
        ];
    }

    protected function getSpaceBefore(int $tokenIndex, Tokens $tokens): ?int
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::PUNCTUATION_TYPE)) {
            return null;
        }

        return $this->punctuationWithSpaceBefore[$token->getValue()] ?? null;
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

        return $this->punctuationWithSpaceAfter[$token->getValue()] ?? null;
    }
}
