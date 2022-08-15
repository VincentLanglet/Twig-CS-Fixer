<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use Exception;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Config\ConfigResolver;

class ConfigResolverTest extends TestCase
{
    /**
     * @dataProvider resolveConfigDataProvider
     */
    public function testResolveConfig(string $workingDir, ?string $configPath, string $configName): void
    {
        $configResolver = new ConfigResolver($workingDir);
        $config = $configResolver->resolveConfig([], $configPath);

        self::assertSame($configName, $config->getName());
    }

    /**
     * @return iterable<array-key, array{string, string|null, string}>
     */
    public function resolveConfigDataProvider(): iterable
    {
        yield [__DIR__.'/Fixtures/directoryWithoutConfig', null, 'Default'];
        yield [__DIR__.'/Fixtures/directoryWithCustomRuleset', null, 'Custom'];
        yield [__DIR__, 'Fixtures/directoryWithCustomRuleset/.twig-cs-fixer.php', 'Custom'];
        yield ['/tmp', __DIR__.'/Fixtures/directoryWithCustomRuleset/.twig-cs-fixer.php', 'Custom'];
    }

    /**
     * @dataProvider resolveConfigExceptionDataProvider
     */
    public function testResolveConfigException(string $workingDir, ?string $path): void
    {
        $configResolver = new ConfigResolver($workingDir);

        self::expectException(Exception::class);
        $configResolver->resolveConfig([], $path);
    }

    /**
     * @return iterable<array-key, array{string, string|null}>
     */
    public function resolveConfigExceptionDataProvider(): iterable
    {
        yield [__DIR__.'/Fixtures/directoryWithInvalidConfig', null];
        yield [__DIR__, 'Fixtures/directoryWithInvalidConfig/.twig-cs-fixer.php'];
        yield [__DIR__, 'Fixtures/path/to/not/found/.twig-cs-fixer.php'];
    }

    /**
     * @param string[] $paths
     *
     * @dataProvider resolveFinderDataProvider
     */
    public function testResolveFinder(array $paths, string $configPath, int $expectedCount): void
    {
        $configResolver = new ConfigResolver(__DIR__);
        $config = $configResolver->resolveConfig($paths, $configPath);

        self::assertCount($expectedCount, $config->getFinder());
    }

    /**
     * @return iterable<array-key, array{array<string>, string, int}>
     */
    public function resolveFinderDataProvider(): iterable
    {
        yield [
            [],
            __DIR__.'/Fixtures/directoryWithCustomFinder/.twig-cs-fixer.php',
            0,
        ];

        yield [
            [__DIR__.'/Fixtures/directoryWithFile'],
            __DIR__.'/Fixtures/directoryWithCustomFinder/.twig-cs-fixer.php',
            1,
        ];

        yield [
            [__DIR__.'/Fixtures/directoryWithFile'],
            __DIR__.'/Fixtures/directoryWithCustomFinder2/.twig-cs-fixer.php',
            2,
        ];
    }

    /**
     * @dataProvider configPathIsCorrectlyGeneratedDataProvider
     */
    public function testConfigPathIsCorrectlyGenerated(string $configPath, string $path): void
    {
        $configResolver = new ConfigResolver('/tmp/path/not/found');

        self::expectExceptionMessage(sprintf('Cannot find the config file "%s".', $configPath));
        $configResolver->resolveConfig([], $path);
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public function configPathIsCorrectlyGeneratedDataProvider(): iterable
    {
        yield ['/tmp/path/not/found/', ''];
        yield ['/tmp/path/not/found/a', 'a'];
        yield ['/tmp/path/not/found/../a', '../a'];
        yield ['/a', '/a'];
        yield ['\\a', '\\a'];
        yield ['C:\WINDOWS', 'C:\WINDOWS'];
    }
}
