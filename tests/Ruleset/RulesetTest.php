<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Ruleset;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\RuleInterface;
use TwigCsFixer\Rules\Whitespace\BlankEOFRule;
use TwigCsFixer\Rules\Whitespace\TrailingSpaceRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\StandardInterface;

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
        $rule = self::createStub(RuleInterface::class);

        $ruleset->addRule($rule);
        static::assertCount(1, $ruleset->getRules());

        $ruleset->removeRule($rule::class);
        static::assertCount(0, $ruleset->getRules());
    }

    public function testAddStandard(): void
    {
        $ruleset = new Ruleset();

        // Using real rule to have different class name
        $rule1 = new BlankEOFRule();
        $rule2 = new TrailingSpaceRule();
        $standard = self::createStub(StandardInterface::class);
        $standard->method('getRules')->willReturn([$rule1, $rule2]);

        $ruleset->addStandard($standard);
        static::assertCount(2, $ruleset->getRules());
    }
}
