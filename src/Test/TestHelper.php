<?php

declare(strict_types=1);

namespace TwigCsFixer\Test;

final class TestHelper
{
    public static function getOsPath(string $path): string
    {
        return str_replace('/', \DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @param string $contents Content to compare
     * @param string $filePath file path to diff the file against
     */
    public static function generateDiff(string $contents, string $filePath): string
    {
        $cwd = getcwd();
        if (false === $cwd) {
            throw new \LogicException('Cannot get the current working directory.');
        }

        $cwd .= \DIRECTORY_SEPARATOR;
        if (str_starts_with($filePath, $cwd)) {
            $filename = substr($filePath, \strlen($cwd));
        } else {
            $filename = $filePath;
        }

        $tempName = tempnam(sys_get_temp_dir(), 'TwigCsFixer');
        if (false === $tempName) {
            throw new \LogicException('Cannot generate temporary name.');
        }

        $fixedFile = fopen($tempName, 'w');
        if (false === $fixedFile) {
            throw new \LogicException(\sprintf('Cannot open temporary file "%s".', $tempName));
        }

        fwrite($fixedFile, $contents);

        // We must use something like shell_exec() because whitespace at the end
        // of lines is critical to diff files.
        $filename = escapeshellarg($filename);
        $cmd = "diff -u -L{$filename} -LTwigCsFixer {$filename} \"{$tempName}\"";

        $diff = shell_exec($cmd);

        fclose($fixedFile);
        if (is_file($tempName)) {
            unlink($tempName);
        }

        $diffLines = [];
        if (null !== $diff && false !== $diff) {
            $diffLines = explode(\PHP_EOL, $diff);
            if (1 === \count($diffLines)) {
                // Seems to be required for cygwin.
                $diffLines = explode("\n", $diff);
            }
        }

        $diff = [];
        foreach ($diffLines as $line) {
            if (isset($line[0])) {
                $diff[] = match ($line[0]) {
                    '-' => "\033[31m{$line}\033[0m",
                    '+' => "\033[32m{$line}\033[0m",
                    default => $line,
                };
            }
        }

        return implode(\PHP_EOL, $diff);
    }
}
