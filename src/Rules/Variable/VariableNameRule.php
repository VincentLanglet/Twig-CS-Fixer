<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;

use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;
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
    public function __construct(
        private string $case = self::SNAKE_CASE,
        private string $optionalPrefix = '',
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'case' => $this->case,
            'optionalPrefix' => $this->optionalPrefix,
        ];
    }

    protected function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);

        if ($token->isMatching(Token::BLOCK_NAME_TYPE, 'set')) {
            $nameTokenIndex = $tokens->findNext(Token::NAME_TYPE, $tokenIndex);
            Assert::notFalse($nameTokenIndex, 'A BLOCK_NAME_TYPE "set" must be followed by a name');

            $this->validateVariable($tokens->get($nameTokenIndex));
        } elseif ($token->isMatching(Token::BLOCK_NAME_TYPE, 'for')) {
            $nameTokenIndex = $tokens->findNext(Token::NAME_TYPE, $tokenIndex);
            Assert::notFalse($nameTokenIndex, 'A BLOCK_NAME_TYPE "for" must be followed by a name');

            $secondNameTokenIndex = $tokens->findNext([Token::NAME_TYPE, Token::OPERATOR_TYPE], $nameTokenIndex + 1);
            Assert::notFalse($secondNameTokenIndex, 'A BLOCK_NAME_TYPE "for" must use the "in" operator');

            $this->validateVariable($tokens->get($nameTokenIndex));
            if ($tokens->get($secondNameTokenIndex)->isMatching(Token::NAME_TYPE)) {
                $this->validateVariable($tokens->get($secondNameTokenIndex));
            }
        }
    }

    private function validateVariable(Token $token): void
    {
        $name = $token->getValue();
        $prefix = '';
        if (str_starts_with($name, $this->optionalPrefix)) {
            $prefix = $this->optionalPrefix;
            $name = substr($name, \strlen($this->optionalPrefix));
        }

        $expected = match ($this->case) {
            self::SNAKE_CASE => StringUtil::toSnakeCase($name),
            self::CAMEL_CASE => StringUtil::toCamelCase($name),
            self::PASCAL_CASE => StringUtil::toPascalCase($name),
        };

        if ($expected !== $name) {
            $this->addError(
                \sprintf('The var name must use %s; expected %s.', $this->case, $prefix.$expected),
                $token,
            );
        }
    }
}
