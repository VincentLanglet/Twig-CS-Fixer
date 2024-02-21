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
            'Fixtures',
        ]);

        static::assertSame(0, $process->run(), $process->getErrorOutput());
    }
}
