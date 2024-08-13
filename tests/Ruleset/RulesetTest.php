<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Ruleset;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\String\SingleQuoteRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\StandardInterface;
use TwigCsFixer\Token\Tokens;

final class RulesetTest extends TestCase
{
    public function testStartWithNoRule(): void
    {
        $ruleset = new Ruleset();
        static::assertSame([], $ruleset->getRules());
    }

    public function testAddAndRemoveRule(): void
    {
        $ruleset = new Ruleset();
        $rule1 = new SingleQuoteRule(true);
        $rule2 = new SingleQuoteRule(false);

        $ruleset->addRule($rule1);
        static::assertCount(1, $ruleset->getRules());

        $ruleset->addRule($rule1);
        static::assertCount(1, $ruleset->getRules());

        $ruleset->addRule($rule2);
        static::assertCount(2, $ruleset->getRules());

        $ruleset->removeRule(SingleQuoteRule::class);
        static::assertCount(0, $ruleset->getRules());

        $ruleset->addRule($rule1);
        static::assertCount(1, $ruleset->getRules());

        $ruleset->overrideRule($rule2);
        static::assertCount(1, $ruleset->getRules());
    }

    public function testAddStandard(): void
    {
        $ruleset = new Ruleset();
        $rule1 = new SingleQuoteRule(true);
        $rule2 = new SingleQuoteRule(false);

        $standard1 = self::createStub(StandardInterface::class);
        $standard1->method('getRules')->willReturn([$rule1]);

        $standard2 = self::createStub(StandardInterface::class);
        $standard2->method('getRules')->willReturn([$rule2]);

        $ruleset->addStandard($standard1);
        static::assertCount(1, $ruleset->getRules());

        $ruleset->addStandard($standard2);
        static::assertCount(2, $ruleset->getRules());

        $ruleset->overrideStandard($standard2);
        static::assertSame([$rule2], $ruleset->getRules());
    }

    public function testAllowNonFixableRules(): void
    {
        $ruleset = new Ruleset();

        $rule1 = new class extends AbstractRule {
            protected function process(int $tokenIndex, Tokens $tokens): void
            {
            }
        };
        $rule2 = new class extends AbstractFixableRule {
            protected function process(int $tokenIndex, Tokens $tokens): void
            {
            }
        };
        $standard = self::createStub(StandardInterface::class);
        $standard->method('getRules')->willReturn([$rule1, $rule2]);

        $ruleset->addStandard($standard);
        $rules = $ruleset->getRules();
        static::assertCount(2, $rules);
        static::assertSame($rule1, $rules[0]);
        static::assertSame($rule2, $rules[1]);

        $ruleset->allowNonFixableRules(false);
        $rules = $ruleset->getRules();
        static::assertCount(1, $rules);
        static::assertSame($rule2, $rules[0]);

        $ruleset->allowNonFixableRules();
        $rules = $ruleset->getRules();
        static::assertCount(2, $rules);
    }
}
