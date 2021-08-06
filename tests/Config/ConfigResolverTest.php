<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Config;

use Exception;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Config\ConfigResolver;

/**
 * Test for ConfigResolver.
 */
class ConfigResolverTest extends TestCase
{
    /**
     * @param string      $cwd
     * @param string|null $path
     * @param string      $configName
     *
     * @return void
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(string $cwd, ?string $path, string $configName): void
    {
        $configResolver = new ConfigResolver($cwd);
        $config = $configResolver->getConfig($path);

        self::assertSame($configName, $config->getName());
    }

    /**
     * @return iterable<array{string, string|null, string}>
     */
    public function getConfigDataProvider(): iterable
    {
        yield [__DIR__.'/data/directoryWithoutConfig', null, 'Default'];
        yield [__DIR__.'/data/directoryWithConfig', null, 'Custom'];
        yield [__DIR__, 'data/directoryWithConfig/.twig-cs-fixer.php', 'Custom'];
    }

    /**
     * @param string      $cwd
     * @param string|null $path
     *
     * @return void
     *
     * @dataProvider getConfigExceptionDataProvider
     */
    public function testGetConfigException(string $cwd, ?string $path): void
    {
        self::expectException(Exception::class);

        $configResolver = new ConfigResolver($cwd);
        $configResolver->getConfig($path);
    }

    /**
     * @return iterable<array{string, string|null, string}>
     */
    public function getConfigExceptionDataProvider(): iterable
    {
        yield [__DIR__.'/data/directoryWithInvalidConfig', null];
        yield [__DIR__, 'data/directoryWithInvalidConfig/.twig-cs-fixer.php'];
        yield [__DIR__, 'data/path/to/not/found/.twig-cs-fixer.php'];
    }
}
