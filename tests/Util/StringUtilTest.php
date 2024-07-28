<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Util;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Util\StringUtil;

final class StringUtilTest extends TestCase
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
        yield ['foo1bar', 'foo1bar'];
        yield ['foo1Bar', 'foo1_bar'];
        yield ['foo_bar1', 'foo_bar1'];
        yield ['foo1_bar', 'foo1_bar'];
        yield ['foo_1bar', 'foo_1bar'];
        yield ['foo_1Bar', 'foo_1_bar'];
        yield ['foo_bar_1', 'foo_bar_1'];
        yield ['foo_1_bar', 'foo_1_bar'];
        yield ['foo__bar', 'foo_bar'];
        yield ['_foo_bar_', 'foo_bar'];
        yield ['foo bar', 'foo_bar'];
        yield ['FOO BAR', 'foo_bar'];
        yield ['foo#/+bar', 'foo_bar'];
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
        yield ['foo1bar', 'foo1bar'];
        yield ['foo1Bar', 'foo1Bar'];
        yield ['foo_bar1', 'fooBar1'];
        yield ['foo1_bar', 'foo1Bar'];
        yield ['foo_1bar', 'foo1bar'];
        yield ['foo_1Bar', 'foo1Bar'];
        yield ['foo_1_bar', 'foo1Bar'];
        yield ['foo__bar', 'fooBar'];
        yield ['_foo_bar_', 'fooBar'];
        yield ['foo bar', 'fooBar'];
        yield ['FOO BAR', 'fooBar'];
        yield ['foo#/+bar', 'fooBar'];
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
        yield ['FOOBar', 'FooBar'];
        yield ['foo1bar', 'Foo1bar'];
        yield ['foo1Bar', 'Foo1Bar'];
        yield ['foo_bar1', 'FooBar1'];
        yield ['foo1_bar', 'Foo1Bar'];
        yield ['foo_1bar', 'Foo1bar'];
        yield ['foo_1Bar', 'Foo1Bar'];
        yield ['foo_bar_1', 'FooBar1'];
        yield ['foo_1_bar', 'Foo1Bar'];
        yield ['foo__bar', 'FooBar'];
        yield ['_foo_bar_', 'FooBar'];
        yield ['foo bar', 'FooBar'];
        yield ['FOO BAR', 'FooBar'];
        yield ['foo#/+bar', 'FooBar'];
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
        yield ['foo1bar', 'foo1bar'];
        yield ['foo1Bar', 'foo1-bar'];
        yield ['foo_bar1', 'foo-bar1'];
        yield ['foo1_bar', 'foo1-bar'];
        yield ['foo_1bar', 'foo-1bar'];
        yield ['foo_1Bar', 'foo-1-bar'];
        yield ['foo_bar_1', 'foo-bar-1'];
        yield ['foo_1_bar', 'foo-1-bar'];
        yield ['foo__bar', 'foo-bar'];
        yield ['_foo_bar_', 'foo-bar'];
        yield ['foo bar', 'foo-bar'];
        yield ['FOO BAR', 'foo-bar'];
        yield ['foo#/+bar', 'foo-bar'];
    }
}
