<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Function;

use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
use TwigCsFixer\Util\StringUtil;

/**
 * Ensures that named argument are in snake_case (Configurable).
 */
final class MacroArgumentNameRule extends AbstractRule implements ConfigurableRuleInterface
{
    // Kebab case is not a valid case for argument.
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

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (!$token->isMatching(Token::MACRO_VAR_NAME_TYPE)) {
            return;
        }

        $name = $token->getValue();
        $expected = match ($this->case) {
            self::SNAKE_CASE => StringUtil::toSnakeCase($name),
            self::CAMEL_CASE => StringUtil::toCamelCase($name),
            self::PASCAL_CASE => StringUtil::toPascalCase($name),
        };

        if ($expected !== $name) {
            $this->addError(
                \sprintf('The macro argument must use %s; expected %s.', $this->case, $expected),
                $token,
            );
        }
    }
}
