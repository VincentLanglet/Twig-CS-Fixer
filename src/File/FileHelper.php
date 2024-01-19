<?php

declare(strict_types=1);

namespace TwigCsFixer\File;

final class FileHelper
{
    /**
     * @param array<string> $ignoredDir
     */
    public static function getFileName(
        string $absolutePath,
        ?string $baseDir = null,
        array $ignoredDir = []
    ): ?string {
        $split = self::splitPath($absolutePath, $baseDir, $ignoredDir);
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
        string $absolutePath,
        ?string $baseDir = null,
        array $ignoredDir = []
    ): array {
        $split = self::splitPath($absolutePath, $baseDir, $ignoredDir);
        array_pop($split);

        return $split;
    }

    /**
     * @param array<string> $ignoredDir
     *
     * @return list<string>
     */
    private static function splitPath(
        string $absolutePath,
        ?string $baseDir = null,
        array $ignoredDir = []
    ): array {
        $baseDir = trim($baseDir ?? '', \DIRECTORY_SEPARATOR);

        if ('' === $baseDir) {
            $baseDirPosition = 0;
            $baseDir = \DIRECTORY_SEPARATOR;
        } else {
            $baseDir = \DIRECTORY_SEPARATOR.$baseDir.\DIRECTORY_SEPARATOR;
            $baseDirPosition = strrpos($absolutePath, $baseDir);
            if (false === $baseDirPosition) {
                return [];
            }
        }

        $path = substr($absolutePath, $baseDirPosition + \strlen($baseDir));
        foreach ($ignoredDir as $ignoredDirectory) {
            $ignoredDirectory = trim($ignoredDirectory, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR;
            if (str_starts_with($path, $ignoredDirectory)) {
                return [];
            }
        }

        return explode(\DIRECTORY_SEPARATOR, $path);
    }
}
