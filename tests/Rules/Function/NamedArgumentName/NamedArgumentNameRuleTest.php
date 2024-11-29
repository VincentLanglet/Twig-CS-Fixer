<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Function\NamedArgumentName;

use TwigCsFixer\Rules\Function\NamedArgumentNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class NamedArgumentNameRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'case' => NamedArgumentNameRule::SNAKE_CASE,
            ],
            (new NamedArgumentNameRule())->getConfiguration()
        );
        static::assertSame(
            [
                'case' => NamedArgumentNameRule::CAMEL_CASE,
            ],
            (new NamedArgumentNameRule(NamedArgumentNameRule::CAMEL_CASE))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new NamedArgumentNameRule(), [
            'NamedArgumentName.Error:1:28' => 'The named argument must use snake_case; expected baz_baz.',
        ]);
    }

    public function testRulePascalCase(): void
    {
        $this->checkRule(new NamedArgumentNameRule(NamedArgumentNameRule::PASCAL_CASE), [
            'NamedArgumentName.Error:1:15' => 'The named argument must use PascalCase; expected BarBar.',
            'NamedArgumentName.Error:1:28' => 'The named argument must use PascalCase; expected BazBaz.',
        ]);
    }

    public function testRuleCamelCase(): void
    {
        $this->checkRule(new NamedArgumentNameRule(NamedArgumentNameRule::CAMEL_CASE), [
            'NamedArgumentName.Error:1:15' => 'The named argument must use camelCase; expected barBar.',
        ]);
    }
}
