<?php

namespace TwigCsFixer\Tests\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotResolveConfigException;
use TwigCsFixer\Exception\CannotWriteCacheException;

class CannotWriteCacheExceptionTest extends TestCase
{
    public function testException(): void
    {
        $previous = new Exception('Error', 42);
        $exception = CannotWriteCacheException::because($previous);
        static::assertSame($previous->getMessage(), $exception->getMessage());
        static::assertSame($previous->getCode(), $exception->getCode());

        $exception = CannotWriteCacheException::locationIsDirectory('file');
        static::assertSame(
            'Cannot write cache file "file" as the location exists as directory.',
            $exception->getMessage()
        );
        static::assertSame(0, $exception->getCode());

        $exception = CannotWriteCacheException::locationIsNotWritable('file');
        static::assertSame(
            'Cannot write to file "file" as it is not writable.',
            $exception->getMessage()
        );
        static::assertSame(0, $exception->getCode());

        $exception = CannotWriteCacheException::missingDirectory('file');
        static::assertSame(
            'Directory of cache file "file" does not exists.',
            $exception->getMessage()
        );
        static::assertSame(0, $exception->getCode());
    }
}
