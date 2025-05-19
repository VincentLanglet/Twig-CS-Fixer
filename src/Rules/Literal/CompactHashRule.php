<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Literal;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that hashes key are not omitted (or omit them when possible).
 */
class CompactHashRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    public function __construct(private bool $compact = false)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'compact' => $this->compact,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        if ($this->compact) {
            $this->ensureImplicitHashKey($tokenIndex, $tokens);
        } else {
            $this->ensureExplicitHashKey($tokenIndex, $tokens);
        }
    }

    private function ensureExplicitHashKey(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::HASH_KEY_NAME_TYPE)) {
            return;
        }

        $next = $tokens->findNext(Token::EMPTY_TOKENS, $tokenIndex + 1, exclude: true);
        Assert::notFalse($next, 'A hash key cannot be the last token.');

        $nextToken = $tokens->get($next);
        if ($nextToken->isMatching(Token::PUNCTUATION_TYPE, ':')) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('Hash key "%s" should be explicit.', $token->getValue()),
            $token
        );
        if (null !== $fixer) {
            $fixer->addContent($tokenIndex, ':'.$token->getValue());
        }
    }

    private function ensureImplicitHashKey(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::PUNCTUATION_TYPE, ':')) {
            return;
        }

        $previous = $tokens->findPrevious(Token::EMPTY_TOKENS, $tokenIndex - 1, exclude: true);
        Assert::notFalse($previous, 'A punctuation cannot be the first token.');

        $previousToken = $tokens->get($previous);
        if (!$previousToken->isMatching(Token::HASH_KEY_NAME_TYPE)) {
            return;
        }

        $next = $tokens->findNext(Token::EMPTY_TOKENS, $tokenIndex + 1, exclude: true);
        Assert::notFalse($next, 'A punctuation cannot be the last token.');

        $nextToken = $tokens->get($next);
        if (!$nextToken->isMatching(Token::NAME_TYPE)) {
            return;
        }

        if ($nextToken->getValue() !== $previousToken->getValue()) {
            return;
        }

        $separator = $tokens->findNext(Token::EMPTY_TOKENS, $next + 1, exclude: true);
        Assert::notFalse($separator, 'A name cannot be the last token.');

        $separatorToken = $tokens->get($separator);
        if (!$separatorToken->isMatching(Token::PUNCTUATION_TYPE, ['}', ','])) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('Hash key "%s" should be implicit.', $previousToken->getValue()),
            $previousToken
        );
        if (null !== $fixer) {
            $fixer->beginChangeSet();
            $fixer->replaceToken($previous, '');

            // Clean whitespaces after the key.
            $index = $previous + 1;
            while ($tokens->get($index)->isMatching(Token::INDENT_TOKENS)) {
                $fixer->replaceToken($index, '');
                ++$index;
            }

            // Clean whitespaces before the `:`.
            $index = $tokenIndex - 1;
            while ($tokens->get($index)->isMatching(Token::INDENT_TOKENS)) {
                $fixer->replaceToken($index, '');
                --$index;
            }

            $fixer->replaceToken($tokenIndex, '');

            // Clean whitespaces after the `:`.
            $index = $tokenIndex + 1;
            while ($tokens->get($index)->isMatching(Token::INDENT_TOKENS)) {
                $fixer->replaceToken($index, '');
                ++$index;
            }
            $fixer->endChangeSet();
        }
    }
}
