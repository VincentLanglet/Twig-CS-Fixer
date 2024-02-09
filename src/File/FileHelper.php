<?php

declare(strict_types=1);

namespace TwigCsFixer\File;

use Webmozart\Assert\Assert;

final class FileHelper
{
    public static function getAbsolutePath(string $path, ?string $workingDir = null): string
    {
        $workingDir ??= @getcwd();
        Assert::notFalse($workingDir, 'Cannot get the current working directory.');

        return self::isAbsolutePath($path) ? $path : $workingDir.\DIRECTORY_SEPARATOR.$path;
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
        $baseDir = self::simplifyPath(self::getAbsolutePath($baseDir ?? '', $workingDir));
        $path = self::simplifyPath(self::getAbsolutePath($path, $workingDir));

        if (!str_starts_with($path, $baseDir.\DIRECTORY_SEPARATOR)) {
            return [];
        }

        foreach ($ignoredDir as $ignoredDirectory) {
            $ignoredDirectory = self::simplifyPath(self::getAbsolutePath($ignoredDirectory, $baseDir));
            if (str_starts_with($path, $ignoredDirectory.\DIRECTORY_SEPARATOR)) {
                return [];
            }
        }

        return explode(\DIRECTORY_SEPARATOR, substr($path, \strlen($baseDir) + 1));
    }

    private static function isAbsolutePath(string $path): bool
    {
        return '' !== $path && (
            '/' === $path[0]
            || '\\' === $path[0]
            || 1 === preg_match('#^[a-zA-Z]:\\\\#', $path)
        );
    }

    private static function simplifyPath(string $absolutePath): string
    {
        if (!self::isAbsolutePath($absolutePath)) {
            throw new \InvalidArgumentException('The path must be absolute.');
        }

        $parts = explode(\DIRECTORY_SEPARATOR, $absolutePath);

        $result = [];
        foreach ($parts as $part) {
            if ('..' === $part) {
                array_pop($result);
            } elseif ('.' !== $part && '' !== $part) {
                $result[] = $part;
            }
        }

        return \DIRECTORY_SEPARATOR.implode(\DIRECTORY_SEPARATOR, $result);
    }
}
