<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Exception;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Exception\CannotResolveConfigException;

class CannotResolveConfigExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = CannotResolveConfigException::fileNotFound('file');
        static::assertSame('Cannot find the config file "file".', $exception->getMessage());
        static::assertSame(0, $exception->getCode());

        $exception = CannotResolveConfigException::fileMustReturnConfig('file');
        static::assertSame(
            'The config file "file" must return a "TwigCsFixer\Config\Config" object.',
            $exception->getMessage()
        );
        static::assertSame(0, $exception->getCode());
    }
}
