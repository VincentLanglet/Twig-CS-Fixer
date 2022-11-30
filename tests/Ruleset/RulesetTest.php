<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Ruleset;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Exception\CannotJsonEncodeException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Sniff\ConfigurableSniffInterface;
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

        // Using real sniff to have different class name
        $sniff1 = new BlankEOFSniff();
        $sniff2 = new TrailingSpaceSniff();
        $standard = $this->createStub(StandardInterface::class);
        $standard->method('getSniffs')->willReturn([$sniff1, $sniff2]);

        $ruleset->addStandard($standard);
        static::assertCount(2, $ruleset->getSniffs());
    }

    public function testSerialize(): void
    {
        $ruleset = new Ruleset();
        $ruleset->addSniff(new BlankEOFSniff());

        static::assertSame('{"TwigCsFixer\\\\Sniff\\\\BlankEOFSniff":null}', $ruleset->serialize());
    }

    public function testSerializeException(): void
    {
        $ruleset = new Ruleset();
        $sniff = $this->createStub(ConfigurableSniffInterface::class);
        $sniff->method('getConfiguration')->willReturn(["\xB1\x31"]);
        $ruleset->addSniff($sniff);

        $this->expectException(CannotJsonEncodeException::class);
        $ruleset->serialize();
    }
}
