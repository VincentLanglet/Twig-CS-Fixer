<?php

declare(strict_types=1);

namespace TwigCsFixer\File;

use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

final class FileHelper
{
    /**
     * @return non-empty-string
     */
    public static function detectEOL(string $content): string
    {
        preg_match("/\r\n?|\n/", $content, $matches);
        /** @phpstan-var list<non-empty-string> $matches */

        return $matches[0] ?? \PHP_EOL;
    }

    public static function normalizePath(string $path, string $separator = \DIRECTORY_SEPARATOR): string
    {
        return str_replace(['\\', '/'], $separator, $path);
    }

    public static function getRelativePath(string $path, string $baseDir): string
    {
        $path = static::normalizePath($path);

        if (
            '' === $baseDir
            || 0 !== stripos($path, $baseDir.\DIRECTORY_SEPARATOR)
        ) {
            return $path;
        }

        return substr($path, \strlen($baseDir) + 1);
    }

    public static function getAbsolutePath(string $path, ?string $workingDir = null): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        $workingDir ??= @getcwd();
        Assert::notFalse($workingDir, 'Cannot get the current working directory.');

        return $workingDir.\DIRECTORY_SEPARATOR.$path;
    }

    public static function removeDot(string $fileName): string
    {
        if (!str_starts_with($fileName, '.')) {
            return $fileName;
        }

        return substr($fileName, 1);
    }

    /**
     * @param array<string> $ignoredDir
     */
    public static function getFileName(
        string $path,
        ?string $baseDir = null,
        array $ignoredDir = [],
        ?string $workingDir = null,
    ): ?string {
        $split = self::splitPath($path, $baseDir, $ignoredDir, $workingDir);
        if ([] === $split) {
            return null;
        }

        return end($split);
    }

    /**
     * @param array<string> $ignoredDir
     *
     * @return list<string>
     */
    public static function getDirectories(
        string $path,
        ?string $baseDir = null,
        array $ignoredDir = [],
        ?string $workingDir = null,
    ): array {
        $split = self::splitPath($path, $baseDir, $ignoredDir, $workingDir);
        array_pop($split);

        return $split;
    }

    /**
     * @param array<string> $ignoredDir
     *
     * @return list<string>
     */
    private static function splitPath(
        string $path,
        ?string $baseDir = null,
        array $ignoredDir = [],
        ?string $workingDir = null,
    ): array {
        $baseDir = Path::canonicalize(self::getAbsolutePath($baseDir ?? '', $workingDir));
        $path = Path::canonicalize(self::getAbsolutePath($path, $workingDir));

        if (!str_starts_with($path, $baseDir.'/')) {
            return [];
        }

        foreach ($ignoredDir as $ignoredDirectory) {
            $ignoredDirectory = Path::canonicalize(self::getAbsolutePath($ignoredDirectory, $baseDir));
            if (str_starts_with($path, $ignoredDirectory.'/')) {
                return [];
            }
        }

        return explode('/', substr($path, \strlen($baseDir) + 1));
    }
}
