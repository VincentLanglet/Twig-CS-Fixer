<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\Component\Finder\Finder;
use Twig\Extension\CoreExtension;
use Twig\Extension\DebugExtension;
use TwigCsFixer\Cache\Manager\NullCacheManager;
use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\TwigCsFixer;

final class ConfigTest extends TestCase
{
    public function testConfigName(): void
    {
        static::assertEquals('Default', (new Config())->getName());
        static::assertEquals('Custom', (new Config('Custom'))->getName());
    }

    public function testConfigRuleset(): void
    {
        $config = new Config();
        $genericStandard = new TwigCsFixer();

        $ruleset = $config->getRuleset();
        static::assertEquals(
            array_values($genericStandard->getRules()),
            array_values($ruleset->getRules())
        );

        $ruleset = new Ruleset();
        $config->setRuleset($ruleset);
        static::assertSame($ruleset, $config->getRuleset());
    }

    public function testConfigFinder(): void
    {
        $config = new Config();
        static::assertInstanceOf(TwigCsFinder::class, $config->getFinder());

        $finder = new Finder();
        $config->setFinder($finder);
        static::assertSame($finder, $config->getFinder());
    }

    public function testConfigCacheManager(): void
    {
        $config = new Config();
        static::assertNull($config->getCacheManager());

        $cacheManager = new NullCacheManager();
        $config->setCacheManager($cacheManager);
        static::assertSame($cacheManager, $config->getCacheManager());
    }

    public function testConfigCacheFile(): void
    {
        $config = new Config();
        static::assertSame('.twig-cs-fixer.cache', $config->getCacheFile());

        $config->setCacheFile('foo');
        static::assertSame('foo', $config->getCacheFile());

        $config->setCacheFile(null);
        static::assertNull($config->getCacheFile());
    }

    public function testConfigTokenParsers(): void
    {
        $config = new Config();

        static::assertSame([], $config->getTokenParsers());

        $tokenParser1 = new DumpTokenParser();
        $tokenParser2 = new TransTokenParser();
        $config->addTokenParser($tokenParser1);
        $config->addTokenParser($tokenParser2);

        static::assertSame([$tokenParser1, $tokenParser2], $config->getTokenParsers());
    }

    public function testConfigTwigExtensions(): void
    {
        $config = new Config();

        static::assertSame([], $config->getTwigExtensions());

        $twigExtension = new CoreExtension();
        $twigExtension2 = new DebugExtension();
        $config->addTwigExtension($twigExtension);
        $config->addTwigExtension($twigExtension2);

        static::assertSame([$twigExtension, $twigExtension2], $config->getTwigExtensions());
    }

    public function testUseOnlyFixableRules(): void
    {
        $config = new Config();

        static::assertTrue($config->getUseOnlyFixableRules());

        $config->useOnlyFixableRules(false);
        static::assertFalse($config->getUseOnlyFixableRules());
    }
}
