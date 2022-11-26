<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

final class Directory
{
    public function __construct(private string $directoryName)
    {
    }

    public function getRelativePathTo(string $file): string
    {
        $file = $this->normalizePath($file);

        if (
            '' === $this->directoryName
            || 0 !== stripos($file, $this->directoryName.\DIRECTORY_SEPARATOR)
        ) {
            return $file;
        }

        return substr($file, \strlen($this->directoryName) + 1);
    }

    private function normalizePath(string $path): string
    {
        return str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $path);
    }
}
