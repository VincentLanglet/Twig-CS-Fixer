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
     * @param string      $workingDir
     * @param string|null $path
     * @param string      $configName
     *
     * @return void
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(string $workingDir, ?string $path, string $configName): void
    {
        $configResolver = new ConfigResolver($workingDir);
        $config = $configResolver->getConfig($path);

        self::assertSame($configName, $config->getName());
    }

    /**
     * @return iterable<array-key, array{string, string|null, string}>
     */
    public function getConfigDataProvider(): iterable
    {
        yield [__DIR__.'/data/directoryWithoutConfig', null, 'Default'];
        yield [__DIR__.'/data/directoryWithConfig', null, 'Custom'];
        yield [__DIR__, 'data/directoryWithConfig/.twig-cs-fixer.php', 'Custom'];
        yield ['/tmp', __DIR__.'/data/directoryWithConfig/.twig-cs-fixer.php', 'Custom'];
    }

    /**
     * @param string      $workingDir
     * @param string|null $path
     *
     * @return void
     *
     * @dataProvider getConfigExceptionDataProvider
     */
    public function testGetConfigException(string $workingDir, ?string $path): void
    {
        self::expectException(Exception::class);

        $configResolver = new ConfigResolver($workingDir);
        $configResolver->getConfig($path);
    }

    /**
     * @return iterable<array-key, array{string, string|null}>
     */
    public function getConfigExceptionDataProvider(): iterable
    {
        yield [__DIR__.'/data/directoryWithInvalidConfig', null];
        yield [__DIR__, 'data/directoryWithInvalidConfig/.twig-cs-fixer.php'];
        yield [__DIR__, 'data/path/to/not/found/.twig-cs-fixer.php'];
    }
}
