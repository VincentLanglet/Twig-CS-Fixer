<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;

use Symfony\Component\String\UnicodeString;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Ensures that variable name use snake_case (Configurable).
 */
final class VariableNameRule extends AbstractRule implements ConfigurableRuleInterface
{
    // Kebab case is not a valid case for variable names.
    public const SNAKE_CASE = 'snake_case';
    public const CAMEL_CASE = 'camelCase';
    public const PASCAL_CASE = 'PascalCase';

    /**
     * @param self::* $case
     */
    public function __construct(private string $case = self::SNAKE_CASE)
    {
    }

    public function getConfiguration(): array
    {
        return [
            'case' => $this->case,
        ];
    }

    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        if (!$this->isTokenMatching($token, Token::BLOCK_NAME_TYPE, 'set')) {
            return;
        }

        $nameTokenPosition = $this->findNext(Token::NAME_TYPE, $tokens, $tokenPosition);
        Assert::notFalse($nameTokenPosition, 'A BLOCK_NAME_TYPE set must be followed by a name');
        $name = $tokens[$nameTokenPosition]->getValue();

        $expected = match ($this->case) {
            self::SNAKE_CASE => (new UnicodeString($name))->snake()->toString(),
            self::CAMEL_CASE => (new UnicodeString($name))->camel()->toString(),
            self::PASCAL_CASE => ucfirst((new UnicodeString($name))->camel()->toString()),
        };

        if ($expected !== $name) {
            $this->addError(
                sprintf('The var name must use %s; expected %s.', $this->case, $expected),
                $token,
            );
        }
    }
}
