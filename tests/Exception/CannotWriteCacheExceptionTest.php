<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Exception;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Exception\CannotWriteCacheException;

final class CannotWriteCacheExceptionTest extends TestCase
{
    public function testException(): void
    {
        $previous = new \JsonException('Error', 42);
        $exception = CannotWriteCacheException::jsonException($previous);
        static::assertSame(
            'Cannot encode cache to JSON, error: "Error".',
            $exception->getMessage()
        );
        static::assertSame($previous->getCode(), $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());

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
