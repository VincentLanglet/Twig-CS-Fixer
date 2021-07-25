<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Exception;

/**
 * TwigCsFixer configuration data.
 */
class Config
{
    /**
     * @var string[]
     */
    protected $paths = [];

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
                $flags = \RecursiveDirectoryIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS;
                $directoryIterator = new \RecursiveDirectoryIterator($path, $flags);
                $filter = new TwigFileFilter($directoryIterator);
                $iterator = new \RecursiveIteratorIterator($filter);

                /** @var \SplFileInfo $file */
                foreach ($iterator as $k => $file) {
                    $files[] = $file->getRealPath();
                }
            } elseif (is_file($path)) {
                $file = new \SplFileInfo($path);
                $files[] = $file->getRealPath();
            } else {
                throw new Exception(sprintf('Unknown path: "%s"', $path));
            }
        }

        return array_filter($files);
    }
}
