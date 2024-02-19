<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Binary;

use Symfony\Component\Process\Process;
use TwigCsFixer\Tests\FileTestCase;
use TwigCsFixer\Tests\TestHelper;

final class TwigCsFixerTest extends FileTestCase
{
    public function testBinary(): void
    {
        $process = Process::fromShellCommandline(sprintf(
            '%s lint Fixtures',
            TestHelper::getOsPath(__DIR__.'/../../bin/twig-cs-fixer')
        ));

        static::assertSame(0, $process->run(), $process->getErrorOutput());
    }
}
