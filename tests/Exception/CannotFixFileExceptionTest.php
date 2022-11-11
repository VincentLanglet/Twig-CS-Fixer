<?php

namespace TwigCsFixer\Tests\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwigCsFixer\Exception\CannotFixFileException;

class CannotFixFileExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = CannotFixFileException::fileNotReadable('file');
        static::assertSame('Cannot read the content of the file "file".', $exception->getMessage());
        static::assertSame(0, $exception->getCode());

        $exception = CannotFixFileException::infiniteLoop('file');
        static::assertSame('Too many iteration while trying to fix file "file".', $exception->getMessage());
        static::assertSame(0, $exception->getCode());
    }
}
