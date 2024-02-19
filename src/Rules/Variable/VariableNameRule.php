<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;

use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Util\StringUtil;
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

        if ($this->isTokenMatching($token, Token::BLOCK_NAME_TYPE, 'set')) {
            $nameTokenPosition = $this->findNext(Token::NAME_TYPE, $tokens, $tokenPosition);
            Assert::notFalse($nameTokenPosition, 'A BLOCK_NAME_TYPE "set" must be followed by a name');

            $this->validateVariable($tokens[$nameTokenPosition]);
        } elseif ($this->isTokenMatching($token, Token::BLOCK_NAME_TYPE, 'for')) {
            $nameTokenPosition = $this->findNext(Token::NAME_TYPE, $tokens, $tokenPosition);
            Assert::notFalse($nameTokenPosition, 'A BLOCK_NAME_TYPE "for" must be followed by a name');

            $secondNameTokenPosition = $this->findNext([Token::NAME_TYPE, Token::OPERATOR_TYPE], $tokens, $nameTokenPosition + 1);
            Assert::notFalse($secondNameTokenPosition, 'A BLOCK_NAME_TYPE "for" must use the "in" operator');

            $this->validateVariable($tokens[$nameTokenPosition]);
            if ($this->isTokenMatching($tokens[$secondNameTokenPosition], Token::NAME_TYPE)) {
                $this->validateVariable($tokens[$secondNameTokenPosition]);
            }
        }
    }

    private function validateVariable(Token $token): void
    {
        $name = $token->getValue();
        $expected = match ($this->case) {
            self::SNAKE_CASE => StringUtil::toSnakeCase($name),
            self::CAMEL_CASE => StringUtil::toCamelCase($name),
            self::PASCAL_CASE => StringUtil::toPascalCase($name),
        };

        if ($expected !== $name) {
            $this->addError(
                sprintf('The var name must use %s; expected %s.', $this->case, $expected),
                $token,
            );
        }
    }
}
