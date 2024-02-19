<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Util;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Util\StringUtil;

class StringUtilTest extends TestCase
{
    /**
     * @dataProvider toSnakeCaseDataProvider
     */
    public function testToSnakeCase(string $string, string $expected): void
    {
        static::assertSame($expected, StringUtil::toSnakeCase($string));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function toSnakeCaseDataProvider(): iterable
    {
        yield ['foobar', 'foobar'];
        yield ['fooBar', 'foo_bar'];
        yield ['foo_bar', 'foo_bar'];
        yield ['foo-bar', 'foo_bar'];
        yield ['FooBar', 'foo_bar'];
        yield ['FOOBar', 'foo_bar'];
    }

    /**
     * @dataProvider toCamelCaseDataProvider
     */
    public function testToCamelCase(string $string, string $expected): void
    {
        static::assertSame($expected, StringUtil::toCamelCase($string));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function toCamelCaseDataProvider(): iterable
    {
        yield ['foobar', 'foobar'];
        yield ['fooBar', 'fooBar'];
        yield ['foo_bar', 'fooBar'];
        yield ['foo-bar', 'fooBar'];
        yield ['FooBar', 'fooBar'];
        yield ['FOOBar', 'fooBar'];
    }

    /**
     * @dataProvider toPascalCaseDataProvider
     */
    public function testToPascalCase(string $string, string $expected): void
    {
        static::assertSame($expected, StringUtil::toPascalCase($string));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function toPascalCaseDataProvider(): iterable
    {
        yield ['foobar', 'Foobar'];
        yield ['fooBar', 'FooBar'];
        yield ['foo_bar', 'FooBar'];
        yield ['foo-bar', 'FooBar'];
        yield ['FooBar', 'FooBar'];
        yield ['FOOBar', 'FOOBar'];
    }

    /**
     * @dataProvider toKebabCaseDataProvider
     */
    public function testToKebabCase(string $string, string $expected): void
    {
        static::assertSame($expected, StringUtil::toKebabCase($string));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function toKebabCaseDataProvider(): iterable
    {
        yield ['foobar', 'foobar'];
        yield ['fooBar', 'foo-bar'];
        yield ['foo_bar', 'foo-bar'];
        yield ['foo-bar', 'foo-bar'];
        yield ['FooBar', 'foo-bar'];
        yield ['FOOBar', 'foo-bar'];
    }
}
