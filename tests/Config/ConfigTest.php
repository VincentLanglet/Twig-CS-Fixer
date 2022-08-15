<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

class ConfigTest extends TestCase
{
    public function testDefaultConfigHaveDefaultName(): void
    {
        self::assertEquals('Default', (new Config())->getName());
    }

    public function testConfigGetName(): void
    {
        self::assertEquals('Custom', (new Config('Custom'))->getName());
    }

    public function testDefaultConfigHaveGenericStandard(): void
    {
        $config = new Config();
        $genericStandard = new Generic();

        $ruleset = $config->getRuleset();
        self::assertEquals(
            array_values($genericStandard->getSniffs()),
            array_values($ruleset->getSniffs())
        );
    }

    public function testSetRulesetOverrideTheDefaultOne(): void
    {
        $ruleset = new Ruleset();

        $config = new Config();
        $config->setRuleset($ruleset);

        self::assertSame($ruleset, $config->getRuleset());
    }

    public function testDefaultConfigHaveDefaultFinder(): void
    {
        $config = new Config();

        self::assertInstanceOf(TwigCsFinder::class, $config->getFinder());
    }

    public function testSetFinderOverrideTheDefaultOne(): void
    {
        $finder = new Finder();

        $config = new Config();
        $config->setFinder($finder);

        self::assertSame($finder, $config->getFinder());
    }
}
