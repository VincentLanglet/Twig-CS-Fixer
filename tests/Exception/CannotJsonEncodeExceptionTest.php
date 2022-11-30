<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Exception;

use JsonException;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Exception\CannotJsonEncodeException;

class CannotJsonEncodeExceptionTest extends TestCase
{
    /**
     * @dataProvider exceptionDataProvider
     */
    public function testException(string $expectedMessage, JsonException $jsonException): void
    {
        $exception = CannotJsonEncodeException::because($jsonException);
        static::assertSame($expectedMessage, $exception->getMessage());
        static::assertSame($jsonException->getCode(), $exception->getCode());
        static::assertSame($jsonException, $exception->getPrevious());
    }

    /**
     * @return iterable<array-key, array{string, JsonException}>
     */
    public function exceptionDataProvider(): iterable
    {
        $suffix = ' If you have non-UTF8 or non-UTF16 chars in your signature,'
            .' consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.';

        yield [
            'Cannot encode to JSON, error: "errorMessage1".',
            new JsonException('errorMessage1'),
        ];
        yield [
            'Cannot encode to JSON, error: "errorMessage2".'.$suffix,
            new JsonException('errorMessage2', \JSON_ERROR_UTF8),
        ];
        yield [
            'Cannot encode to JSON, error: "errorMessage3".'.$suffix,
            new JsonException('errorMessage3', \JSON_ERROR_UTF16),
        ];
    }
}
