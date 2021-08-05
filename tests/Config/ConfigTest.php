<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

/**
 * Test for Config.
 */
class ConfigTest extends TestCase
{
    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testSetRulesetOverrideTheDefaultOne(): void
    {
        $ruleset = new Ruleset();

        $config = new Config();
        $config->setRuleset($ruleset);

        self::assertSame($ruleset, $config->getRuleset());
    }
}
