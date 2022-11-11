<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class TwigCsFixerTest extends FileTestCase
{
    public function testBinary(): void
    {
        $process = Process::fromShellCommandline(sprintf('%s lint', __DIR__.'/../bin/twig-cs-fixer'));

        static::assertSame(0, $process->run());
    }
}
