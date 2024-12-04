<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Function\MacroArgumentName;

use TwigCsFixer\Rules\Function\MacroArgumentNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class MacroArgumentNameRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'case' => MacroArgumentNameRule::SNAKE_CASE,
            ],
            (new MacroArgumentNameRule())->getConfiguration()
        );
        static::assertSame(
            [
                'case' => MacroArgumentNameRule::CAMEL_CASE,
            ],
            (new MacroArgumentNameRule(MacroArgumentNameRule::CAMEL_CASE))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new MacroArgumentNameRule(), [
            'MacroArgumentName.Error:1:14' => 'The macro argument must use snake_case; expected foo_bar1.',
            'MacroArgumentName.Error:1:33' => 'The macro argument must use snake_case; expected foo_bar3.',
        ]);
    }

    public function testRulePascalCase(): void
    {
        $this->checkRule(new MacroArgumentNameRule(MacroArgumentNameRule::PASCAL_CASE), [
            'MacroArgumentName.Error:1:14' => 'The macro argument must use PascalCase; expected FooBar1.',
            'MacroArgumentName.Error:1:23' => 'The macro argument must use PascalCase; expected FooBar2.',
        ]);
    }

    public function testRuleCamelCase(): void
    {
        $this->checkRule(new MacroArgumentNameRule(MacroArgumentNameRule::CAMEL_CASE), [
            'MacroArgumentName.Error:1:23' => 'The macro argument must use camelCase; expected fooBar2.',
            'MacroArgumentName.Error:1:33' => 'The macro argument must use camelCase; expected fooBar3.',
        ]);
    }
}
