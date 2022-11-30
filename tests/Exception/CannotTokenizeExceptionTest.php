<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Exception;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Exception\CannotTokenizeException;

final class CannotTokenizeExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = CannotTokenizeException::unclosedBracket('#', 42);
        static::assertSame('Unclosed "#" at line 42.', $exception->getMessage());
        static::assertSame(0, $exception->getCode());

        $exception = CannotTokenizeException::unclosedComment(42);
        static::assertSame('Unclosed comment at line 42.', $exception->getMessage());
        static::assertSame(0, $exception->getCode());

        $exception = CannotTokenizeException::unexpectedCharacter('#', 42);
        static::assertSame('Unexpected character "#" at line 42.', $exception->getMessage());
        static::assertSame(0, $exception->getCode());

        $exception = CannotTokenizeException::unknownError();
        static::assertSame('The template is invalid.', $exception->getMessage());
        static::assertSame(0, $exception->getCode());
    }
}
