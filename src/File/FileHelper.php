<?php

declare(strict_types=1);

namespace TwigCsFixer\File;

final class FileHelper
{
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
        array $ignoredDir = []
    ): ?string {
        $split = self::splitPath($path, $baseDir, $ignoredDir);
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
        array $ignoredDir = []
    ): array {
        $split = self::splitPath($path, $baseDir, $ignoredDir);
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
        array $ignoredDir = []
    ): array {
        $baseDir = trim($baseDir ?? '', \DIRECTORY_SEPARATOR);

        if ('' === $baseDir || str_starts_with($path, $baseDir.\DIRECTORY_SEPARATOR)) {
            $baseDirPosition = 0;
            $baseDir .= \DIRECTORY_SEPARATOR;
        } else {
            $baseDir = \DIRECTORY_SEPARATOR.$baseDir.\DIRECTORY_SEPARATOR;
            $baseDirPosition = strrpos($path, $baseDir);
            if (false === $baseDirPosition) {
                return [];
            }
        }

        $path = substr($path, $baseDirPosition + \strlen($baseDir));
        foreach ($ignoredDir as $ignoredDirectory) {
            $ignoredDirectory = trim($ignoredDirectory, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR;
            if (str_starts_with($path, $ignoredDirectory)) {
                return [];
            }
        }

        return explode(\DIRECTORY_SEPARATOR, $path);
    }
}
