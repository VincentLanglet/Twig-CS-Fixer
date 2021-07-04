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
     * @var array
     */
    protected $paths = [];

    /**
     * @param array $paths
     *
     * @return void
     */
    public function __construct(array $paths = [])
    {
        $this->paths = $paths;
    }

    /**
     * @return array
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

        return $files;
    }
}
