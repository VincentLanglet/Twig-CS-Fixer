<?php

declare(strict_types=1);

namespace TwigCsFixer\File;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * This class list every file which need to be linted.
 */
final class Finder
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * @param string[] $paths
     *
     * @return void
     */
    public function __construct(array $paths = [])
    {
        $this->paths = $paths;
    }

    /**
     * @return string[]
     *
     * @throws Exception
     */
    public function findFiles(): array
    {
        $files = [];
        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                $flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS;
                $directoryIterator = new RecursiveDirectoryIterator($path, $flags);
                $filter = new TwigFileFilter($directoryIterator);
                $iterator = new RecursiveIteratorIterator($filter);

                /** @var SplFileInfo $file */
                foreach ($iterator as $file) {
                    $files[] = $file->getRealPath();
                }
            } elseif (is_file($path)) {
                $file = new SplFileInfo($path);
                $files[] = $file->getRealPath();
            } else {
                throw new Exception(sprintf('Unknown path: "%s"', $path));
            }
        }

        return array_filter($files);
    }
}
