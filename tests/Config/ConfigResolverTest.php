<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Cache\Manager\FileCacheManager;
use TwigCsFixer\Cache\Manager\NullCacheManager;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Config\ConfigResolver;
use TwigCsFixer\Exception\CannotResolveConfigException;
use TwigCsFixer\Tests\FileTestCase;

class ConfigResolverTest extends FileTestCase
{
    public function testResolveConfigDefaultValues(): void
    {
        $dir = $this->getTmpPath(__DIR__.'/Fixtures/directoryWithoutConfig');
        $configResolver = new ConfigResolver($dir);
        $config = $configResolver->resolveConfig();

        static::assertGreaterThan(0, $config->getFinder()->count());
        static::assertSame(Config::DEFAULT_CACHE_PATH, $config->getCacheFile());
        static::assertInstanceOf(FileCacheManager::class, $config->getCacheManager());
    }

    /**
     * @dataProvider resolveConfigDataProvider
     */
    public function testResolveConfig(string $workingDir, ?string $configPath, string $configName): void
    {
        $configResolver = new ConfigResolver($workingDir);
        $config = $configResolver->resolveConfig([], $configPath);

        static::assertSame($configName, $config->getName());
    }

    /**
     * @return iterable<array-key, array{string, string|null, string}>
     */
    public function resolveConfigDataProvider(): iterable
    {
        yield [$this->getTmpPath(__DIR__.'/Fixtures/directoryWithoutConfig'), null, 'Default'];
        yield [$this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomRuleset'), null, 'Custom'];
        yield [$this->getTmpPath(__DIR__), 'Fixtures/directoryWithCustomRuleset/.twig-cs-fixer.php', 'Custom'];
        yield ['/tmp', $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomRuleset/.twig-cs-fixer.php'), 'Custom'];
    }

    /**
     * @dataProvider resolveConfigExceptionDataProvider
     */
    public function testResolveConfigException(string $workingDir, ?string $path): void
    {
        $configResolver = new ConfigResolver($workingDir);

        self::expectException(CannotResolveConfigException::class);
        $configResolver->resolveConfig([], $path);
    }

    /**
     * @return iterable<array-key, array{string, string|null}>
     */
    public function resolveConfigExceptionDataProvider(): iterable
    {
        yield [$this->getTmpPath(__DIR__.'/Fixtures/directoryWithInvalidConfig'), null];
        yield [$this->getTmpPath(__DIR__), 'Fixtures/directoryWithInvalidConfig/.twig-cs-fixer.php'];
        yield [$this->getTmpPath(__DIR__), 'Fixtures/path/to/not/found/.twig-cs-fixer.php'];
        yield [$this->getTmpPath(__DIR__), ''];
    }

    /**
     * @param string[] $paths
     *
     * @dataProvider resolveFinderDataProvider
     */
    public function testResolveFinder(array $paths, string $configPath, int $expectedCount): void
    {
        $configResolver = new ConfigResolver($this->getTmpPath(__DIR__));
        $config = $configResolver->resolveConfig($paths, $configPath);

        static::assertCount($expectedCount, $config->getFinder());
    }

    /**
     * @return iterable<array-key, array{array<string>, string, int}>
     */
    public function resolveFinderDataProvider(): iterable
    {
        yield [
            [],
            $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomFinder/.twig-cs-fixer.php'),
            0,
        ];

        yield [
            [$this->getTmpPath(__DIR__.'/Fixtures/directoryWithFile')],
            $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomFinder/.twig-cs-fixer.php'),
            1,
        ];

        yield [
            [$this->getTmpPath(__DIR__.'/Fixtures/directoryWithFile')],
            $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomFinder2/.twig-cs-fixer.php'),
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

    /**
     * @param class-string<CacheManagerInterface>|null $expectedCacheManager
     *
     * @dataProvider resolveCacheManagerDataProvider
     */
    public function testResolveCacheManager(
        ?string $configPath,
        bool $disableCache,
        ?string $expectedCacheFile,
        ?string $expectedCacheManager
    ): void {
        $configResolver = new ConfigResolver($this->getTmpPath(__DIR__));
        $config = $configResolver->resolveConfig([], $configPath, $disableCache);

        static::assertSame($expectedCacheFile, $config->getCacheFile());

        if (null === $expectedCacheManager) {
            static::assertNull($config->getCacheManager());
        } else {
            static::assertInstanceOf($expectedCacheManager, $config->getCacheManager());
        }
    }

    /**
     * @return iterable<array-key, array{string|null, bool, string|null, class-string<CacheManagerInterface>|null}>
     */
    public function resolveCacheManagerDataProvider(): iterable
    {
        yield [null, false, Config::DEFAULT_CACHE_PATH,  FileCacheManager::class];
        yield [null, true, null, null];

        $path = $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomCacheManager/.twig-cs-fixer.php');
        yield [$path, false, Config::DEFAULT_CACHE_PATH, NullCacheManager::class];
        yield [$path, true, null, null];

        $path = $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomCacheFile/.twig-cs-fixer.php');
        $cachePath = $this->getTmpPath(__DIR__.'/Fixtures/directoryWithCustomCacheFile/.twig-cs-fixer.cache');
        yield [$path, false, $cachePath, FileCacheManager::class];
        yield [$path, true, null, null];

        $path = $this->getTmpPath(__DIR__.'/Fixtures/directoryWithNoCacheFile/.twig-cs-fixer.php');
        yield [$path, false, null, null];
        yield [$path, true, null, null];
    }

    public function testResolveCacheManagerWithCacheDisabled(): void
    {
        $configResolver = new ConfigResolver($this->getTmpPath(__DIR__));
        $config = $configResolver->resolveConfig([], null, true);

        static::assertNull($config->getCacheManager());
    }
}
