<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Binary;

use Composer\InstalledVersions;
use Symfony\Component\Process\Process;
use TwigCsFixer\Console\Application;
use TwigCsFixer\Test\TestHelper;
use TwigCsFixer\Tests\FileTestCase;

/**
 * @group skip-windows
 */
final class TwigCsFixerTest extends FileTestCase
{
    public function testBinary(): void
    {
        $process = new Process([
            TestHelper::getOsPath(__DIR__.'/../../bin/twig-cs-fixer'),
            'lint',
            'Fixtures',
        ]);

        static::assertSame(0, $process->run(), $process->getErrorOutput());
        static::assertStringContainsString('OK', $process->getOutput());
    }

    public function testBinaryVersion(): void
    {
        $process = new Process([
            TestHelper::getOsPath(__DIR__.'/../../bin/twig-cs-fixer'),
            '--version',
        ]);

        static::assertSame(0, $process->run(), $process->getErrorOutput());

        $version = InstalledVersions::getPrettyVersion(Application::PACKAGE_NAME) ?? '';
        static::assertStringContainsString('Twig-CS-Fixer '.$version, $process->getOutput());
    }
}
