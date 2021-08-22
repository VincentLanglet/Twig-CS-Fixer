<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests;

use LogicException;

use function count;
use function escapeshellarg;
use function explode;
use function fclose;
use function fopen;
use function fwrite;
use function getcwd;
use function implode;
use function is_file;
use function shell_exec;
use function sprintf;
use function strlen;
use function strpos;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/**
 * Helper for tests.
 */
final class TestHelper
{
    /**
     * @param string $contents Content to compare
     * @param string $filePath File path to diff the file against.
     *
     * @return string
     */
    public static function generateDiff(string $contents, string $filePath): string
    {
        $cwd = getcwd();
        if (false === $cwd) {
            throw new LogicException('Cannot get the current working directory.');
        }

        $cwd = $cwd.DIRECTORY_SEPARATOR;
        if (strpos($filePath, $cwd) === 0) {
            $filename = substr($filePath, strlen($cwd));
        } else {
            $filename = $filePath;
        }

        $tempName = tempnam(sys_get_temp_dir(), 'TwigCsFixer');
        if (false === $tempName) {
            throw new LogicException('Cannot generate temporary name.');
        }

        $fixedFile = fopen($tempName, 'w');
        if (false === $fixedFile) {
            throw new LogicException(sprintf('Cannot open temporary file "%s".', $tempName));
        }

        fwrite($fixedFile, $contents);

        // We must use something like shell_exec() because whitespace at the end
        // of lines is critical to diff files.
        $filename = escapeshellarg($filename);
        $cmd = "diff -u -L$filename -LTwigCsFixer $filename \"$tempName\"";

        /** @psalm-suppress ForbiddenCode */
        $diff = shell_exec($cmd);

        fclose($fixedFile);
        if (is_file($tempName)) {
            unlink($tempName);
        }

        $diffLines = [];
        if (null !== $diff && false !== $diff) {
            $diffLines = explode(PHP_EOL, $diff);
            if (count($diffLines) === 1) {
                // Seems to be required for cygwin.
                $diffLines = explode("\n", $diff);
            }
        }

        $diff = [];
        foreach ($diffLines as $line) {
            if (isset($line[0])) {
                switch ($line[0]) {
                    case '-':
                        $diff[] = "\033[31m$line\033[0m";
                        break;
                    case '+':
                        $diff[] = "\033[32m$line\033[0m";
                        break;
                    default:
                        $diff[] = $line;
                }
            }
        }

        return implode(PHP_EOL, $diff);
    }
}
