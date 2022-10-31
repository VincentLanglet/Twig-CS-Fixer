<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Composer\InstalledVersions;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use TwigCsFixer\Cache\CacheManagerInterface;
use TwigCsFixer\Cache\Directory;
use TwigCsFixer\Cache\DirectoryInterface;
use TwigCsFixer\Cache\FileCacheManager;
use TwigCsFixer\Cache\FileHandler;
use TwigCsFixer\Cache\NullCacheManager;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

/**
 * Main entry point to config the TwigCsFixer.
 */
final class Config
{
    private string $name;

    private Ruleset $ruleset;

    private Finder $finder;

    private ?string $cacheFile = '.twig-cs-fixer.cache';

    private ?CacheManagerInterface $cacheManager = null;

    private ?Directory $directory = null;

    private string $cwd;

    public function __construct(string $name = 'Default', ?string $cwd = null)
    {
        $this->name = $name;
        $this->ruleset = new Ruleset();
        $this->ruleset->addStandard(new Generic());
        $this->finder = new TwigCsFinder();
        $workingDir = getcwd();
        $this->cwd = $cwd ?? (false !== $workingDir ? $workingDir : __DIR__);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRuleset(): Ruleset
    {
        return $this->ruleset;
    }

    /**
     * @return $this
     */
    public function setRuleset(Ruleset $ruleset): self
    {
        $this->ruleset = $ruleset;

        return $this;
    }

    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * @return $this
     */
    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function getCacheManager(): CacheManagerInterface
    {
        if (null === $this->cacheManager) {
            $cacheFile = $this->getCacheFile();

            if (null === $cacheFile) {
                $this->cacheManager = new NullCacheManager();
            } else {
                $this->cacheManager = new FileCacheManager(
                    new FileHandler($cacheFile),
                    new Signature(
                        \PHP_VERSION,
                        InstalledVersions::getReference('vincentlanglet/twig-cs-fixer') ?? '0',
                        $this->getRuleset()
                    ),
                    $this->getDirectory()
                );
            }
        }

        return $this->cacheManager;
    }

    public function getDirectory(): DirectoryInterface
    {
        if (null === $this->directory) {
            $path = $this->getCacheFile();
            if (null === $path) {
                $absolutePath = $this->cwd;
            } else {
                $filesystem = new Filesystem();

                $absolutePath = $filesystem->isAbsolutePath($path)
                    ? $path
                    : $this->cwd.\DIRECTORY_SEPARATOR.$path;
            }

            $this->directory = new Directory(\dirname($absolutePath));
        }

        return $this->directory;
    }

    public function getCacheFile(): ?string
    {
        return $this->cacheFile;
    }

    /**
     * @return $this
     */
    public function setCacheFile(string $cacheFile): self
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }
}
