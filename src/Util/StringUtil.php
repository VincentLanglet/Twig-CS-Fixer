<?php

declare(strict_types=1);

namespace TwigCsFixer\Util;

use Symfony\Component\String\UnicodeString;

final class StringUtil
{
    /**
     * @see UnicodeString::camel()
     * @see UnicodeString::snake()
     */
    public static function toSnakeCase(string $string): string
    {
        return (new UnicodeString($string))
            ->replaceMatches('/[^\pL_0-9]++/u', '_')
            ->replaceMatches('/(\p{Lu}+)(\p{Lu}\p{Ll})/u', '\1_\2')
            ->replaceMatches('/([\p{Ll}0-9])(\p{Lu})/u', '\1_\2')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->lower()
            ->toString();
    }

    public static function toCamelCase(string $string): string
    {
        return (new UnicodeString($string))->camel()->toString();
    }

    public static function toPascalCase(string $string): string
    {
        return ucfirst(static::toCamelCase($string));
    }

    public static function toKebabCase(string $string): string
    {
        return str_replace('_', '-', self::toSnakeCase($string));
    }
}
