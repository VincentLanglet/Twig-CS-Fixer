<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Variable\VariableName;

use TwigCsFixer\Rules\Variable\VariableNameRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class VariableNameRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['case' => VariableNameRule::SNAKE_CASE],
            (new VariableNameRule())->getConfiguration()
        );

        static::assertSame(
            ['case' => VariableNameRule::PASCAL_CASE],
            (new VariableNameRule(VariableNameRule::PASCAL_CASE))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new VariableNameRule(), [
            'VariableName.Error:2:8' => 'The var name must use snake_case; expected foo_bar.',
            'VariableName.Error:4:8' => 'The var name must use snake_case; expected foo_bar.',
            'VariableName.Error:6:8' => 'The var name must use snake_case; expected user_foo.',
            'VariableName.Error:7:8' => 'The var name must use snake_case; expected key_foo.',
            'VariableName.Error:7:16' => 'The var name must use snake_case; expected user_foo.',
            'VariableName.Error:9:8' => 'The var name must use snake_case; expected foo_bar.',
        ]);
    }

    public function testRuleCamelCase(): void
    {
        $this->checkRule(new VariableNameRule(VariableNameRule::CAMEL_CASE), [
            'VariableName.Error:3:8' => 'The var name must use camelCase; expected fooBar.',
            'VariableName.Error:4:8' => 'The var name must use camelCase; expected fooBar.',
            'VariableName.Error:9:8' => 'The var name must use camelCase; expected fooBar.',
        ]);
    }

    public function testRulePascalCase(): void
    {
        $this->checkRule(new VariableNameRule(VariableNameRule::PASCAL_CASE), [
            'VariableName.Error:1:8' => 'The var name must use PascalCase; expected Foo.',
            'VariableName.Error:2:8' => 'The var name must use PascalCase; expected FooBar.',
            'VariableName.Error:3:8' => 'The var name must use PascalCase; expected FooBar.',
            'VariableName.Error:6:8' => 'The var name must use PascalCase; expected UserFoo.',
            'VariableName.Error:7:8' => 'The var name must use PascalCase; expected KeyFoo.',
            'VariableName.Error:7:16' => 'The var name must use PascalCase; expected UserFoo.',
        ]);
    }
}
