<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Binary;

use Symfony\Component\Process\Process;
use TwigCsFixer\Tests\FileTestCase;
use TwigCsFixer\Tests\TestHelper;

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
            'Fixtures/file.twig',
        ]);

        static::assertSame(0, $process->run(), $process->getErrorOutput());
        static::assertStringContainsString('OK', $process->getOutput());
    }

    /**
     * @dataProvider aliasesDataProvider
     */
    public function testAliases(string $alias, bool $shouldFix): void
    {
        $process = new Process([
            TestHelper::getOsPath(__DIR__.'/../../bin/twig-cs-fixer'),
            $alias,
            'Fixtures',
        ]);

        if ($shouldFix) {
            static::assertSame(0, $process->run());
            static::assertStringContainsString('OK', $process->getOutput());
        } else {
            static::assertSame(1, $process->run());
            static::assertStringContainsString('KO', $process->getOutput());
        }
    }

    /**
     * @return iterable<array-key, array{string, bool}>
     */
    public static function aliasesDataProvider(): iterable
    {
        yield ['check', false];
        yield ['fix', true];
    }
}
