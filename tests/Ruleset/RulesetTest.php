<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Ruleset;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Sniff\OperatorSpacingSniff;
use TwigCsFixer\Sniff\SniffInterface;
use TwigCsFixer\Sniff\TrailingSpaceSniff;
use TwigCsFixer\Standard\StandardInterface;

class RulesetTest extends TestCase
{
    public function testStartWithNoSniff(): void
    {
        $ruleset = new Ruleset();
        static::assertSame([], $ruleset->getSniffs());
    }

    public function testAddAndRemoveSniff(): void
    {
        $ruleset = new Ruleset();
        $sniff = $this->createStub(SniffInterface::class);

        $ruleset->addSniff($sniff);
        static::assertCount(1, $ruleset->getSniffs());

        $ruleset->removeSniff($sniff::class);
        static::assertCount(0, $ruleset->getSniffs());
    }

    public function testAddStandard(): void
    {
        $ruleset = new Ruleset();

        // Using real sniff to have different classFQN
        $sniff1 = new BlankEOFSniff();
        $sniff2 = new TrailingSpaceSniff();
        $standard = $this->createStub(StandardInterface::class);
        $standard->method('getSniffs')->willReturn([$sniff1, $sniff2]);

        $ruleset->addStandard($standard);
        static::assertCount(2, $ruleset->getSniffs());
    }

    public function testEquals(): void
    {
        $ruleset1 = new Ruleset();
        $ruleset1->addSniff(new TrailingSpaceSniff());
        $ruleset2 = new Ruleset();
        $ruleset2->addSniff(new OperatorSpacingSniff());

        static::assertFalse($ruleset1->equals($ruleset2));
        static::assertFalse($ruleset2->equals($ruleset1));

        $ruleset1->addSniff(new OperatorSpacingSniff());
        $ruleset2->addSniff(new TrailingSpaceSniff());

        static::assertTrue($ruleset1->equals($ruleset2));
        static::assertTrue($ruleset2->equals($ruleset1));
    }
}
