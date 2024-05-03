<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\String;

use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;
use Webmozart\Assert\Assert;

/**
 * Ensures that hashes key use quotes (or not).
 */
final class HashQuoteRule extends AbstractFixableRule implements ConfigurableRuleInterface
{
    public function __construct(private bool $useQuote = true)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'useQuote' => $this->useQuote,
        ];
    }

    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::PUNCTUATION_TYPE, ':')) {
            return;
        }

        $previous = $this->findPrevious(Token::EMPTY_TOKENS, $tokens, $tokenPosition - 1, true);
        Assert::notFalse($previous, 'A punctuation cannot be the first token.');

        if ($this->useQuote) {
            $this->nameShouldBeString($previous, $tokens);
        } else {
            $this->stringShouldBeName($previous, $tokens);
        }
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function nameShouldBeString(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        $value = $token->getValue();
        $error = sprintf('The hash key "%s" should be quoted.', $value);

        if ($this->isTokenMatching($token, Token::NUMBER_TYPE)) {
            // A value like `012` or `12.3` is cast to `12` by twig,
            // so we let the developer chose the right value.
            $fixable = $this->isInteger($value);
        } elseif ($this->isTokenMatching($token, Token::NAME_TYPE)) {
            $fixable = true;
        } else {
            return;
        }

        $fixer = $fixable
            ? $this->addFixableError($error, $token)
            : $this->addError($error, $token);

        if ($fixer instanceof FixerInterface) {
            $success = $fixer->replaceToken($tokenPosition, '\''.$value.'\'');
        }
    }

    /**
     * @param array<int, Token> $tokens
     */
    private function stringShouldBeName(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::STRING_TYPE)) {
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
            sprintf('The hash key "%s" does not require to be quoted.', $expectedValue),
            $token
        );
        if (null !== $fixer) {
            $fixer->replaceToken($tokenPosition, $expectedValue);
        }
    }

    private function isInteger(string $value): bool
    {
        return $value === (string) (int) $value;
    }
}
