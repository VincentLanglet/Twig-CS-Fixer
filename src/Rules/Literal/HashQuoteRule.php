<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Literal;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\Tokens;
use Webmozart\Assert\Assert;

/**
 * Ensures that hashes key are not unnecessarily quoted (or enforce them).
 */
final class HashQuoteRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    public function __construct(private bool $useQuote = false)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'useQuote' => $this->useQuote,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::PUNCTUATION_TYPE, ':')) {
            return;
        }

        $previous = $tokens->findPrevious(Token::EMPTY_TOKENS, $tokenIndex - 1, exclude: true);
        Assert::notFalse($previous, 'A punctuation cannot be the first token.');

        if ($this->useQuote) {
            $this->nameShouldBeString($previous, $tokens);
        } else {
            $this->stringShouldBeName($previous, $tokens);
        }
    }

    private function nameShouldBeString(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);

        $value = $token->getValue();
        $error = \sprintf('The hash key "%s" should be quoted.', $value);

        if ($token->isMatching(Token::NUMBER_TYPE)) {
            // A value like `012` or `12.3` is cast to `12` by twig,
            // so we let the developer chose the right value.
            $fixable = $this->isInteger($value);
        } elseif ($token->isMatching(Token::HASH_KEY_NAME_TYPE)) {
            $fixable = true;
        } else {
            return;
        }

        $fixer = $fixable
            ? $this->addFixableError($error, $token)
            : $this->addError($error, $token);

        if ($fixer instanceof FixerInterface) {
            $fixer->replaceToken($tokenIndex, '\''.$value.'\'');
        }
    }

    private function stringShouldBeName(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::STRING_TYPE)) {
            return;
        }

        $expectedValue = substr($token->getValue(), 1, -1);
        if (
            !$this->isInteger($expectedValue)
            && 1 !== preg_match('/^'.Tokenizer::NAME_PATTERN.'$/', $expectedValue)
        ) {
            return;
        }

        $fixer = $this->addFixableError(
            \sprintf('The hash key "%s" does not require to be quoted.', $expectedValue),
            $token
        );
        if (null !== $fixer) {
            $fixer->replaceToken($tokenIndex, $expectedValue);
        }
    }

    private function isInteger(string $value): bool
    {
        return $value === (string) (int) $value;
    }
}
