<?php

declare(strict_types=1);

namespace TwigCsFixer\Util;

use Symfony\Component\String\UnicodeString;

class StringUtil
{
    public static function toSnakeCase(string $string): string
    {
        return (new UnicodeString($string))->snake()->toString();
    }

    public static function toCamelCase(string $string): string
    {
        return (new UnicodeString($string))->snake()->camel()->toString();
    }

    public static function toPascalCase(string $string): string
    {
        return ucfirst((new UnicodeString($string))->camel()->toString());
    }

    public static function toKebabCase(string $string): string
    {
        return (new UnicodeString($string))->snake()->replace('_', '-')->toString();
    }
}
