<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Composer\InstalledVersions;
use LogicException;
use Symfony\Component\Finder\Finder;
use TwigCsFixer\Cache\FileHandler\CacheFileHandler;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Cache\Manager\FileCacheManager;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Exception\CannotResolveConfigException;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Ruleset\Ruleset;

/**
 * Resolve config from `.twig-cs-fixer.php` if provided
 */
final class ConfigResolver
{
    private const PACKAGE_NAME = 'vincentlanglet/twig-cs-fixer';

    public function __construct(private string $workingDir)
    {
    }

    /**
     * @param string[] $paths
     *
     * @throws CannotResolveConfigException
     */
    public function resolveConfig(
        array $paths = [],
        ?string $configPath = null,
        bool $disableCache = false
    ): Config {
        $config = $this->getConfig($configPath);
        $config->setFinder($this->resolveFinder($config->getFinder(), $paths));

        if ($disableCache) {
            $config->setCacheFile(null);
            $config->setCacheManager(null);
        } else {
            $config->setCacheManager($this->resolveCacheManager(
                $config->getCacheManager(),
                $config->getCacheFile(),
                $config->getRuleset()
            ));
        }

        return $config;
    }

    /**
     * @throws CannotResolveConfigException
     */
    private function getConfig(?string $configPath = null): Config
    {
        if (null !== $configPath) {
            return $this->getConfigFromPath($this->getAbsolutePath($configPath));
        }

        $defaultPath = $this->getAbsolutePath(Config::DEFAULT_PATH);
        if (file_exists($defaultPath)) {
            return $this->getConfigFromPath($defaultPath);
        }

        return new Config();
    }

    /**
     * @throws CannotResolveConfigException
     */
    private function getConfigFromPath(string $configPath): Config
    {
        if (!is_file($configPath)) {
            throw CannotResolveConfigException::fileNotFound($configPath);
        }

        $config = require $configPath;
        if (!$config instanceof Config) {
            throw CannotResolveConfigException::fileMustReturnConfig($configPath);
        }

        return $config;
    }

    /**
     * @param string[] $paths
     */
    private function resolveFinder(Finder $finder, array $paths): Finder
    {
        $nestedFinder = null;
        try {
            $nestedFinder = $finder->getIterator();
        } catch (LogicException) {
            // Only way to know if in() method has not been called
        }

        if ([] === $paths) {
            if (null === $nestedFinder) {
                return $finder->in('./');
            }

            return $finder;
        }

        $files = [];
        $directories = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = $path;
            } else {
                $directories[] = $path;
            }
        }

        if (null === $nestedFinder) {
            return $finder->in($directories)->append($files);
        }

        return TwigCsFinder::create()->in($directories)->append($files);
    }

    private function resolveCacheManager(
        ?CacheManagerInterface $cacheManager,
        ?string $cacheFile,
        Ruleset $ruleset
    ): ?CacheManagerInterface {
        if (null !== $cacheManager) {
            return $cacheManager;
        }

        if (null === $cacheFile) {
            return null;
        }

        return new FileCacheManager(
            new CacheFileHandler($this->getAbsolutePath($cacheFile)),
            new Signature(
                \PHP_VERSION,
                InstalledVersions::getReference(self::PACKAGE_NAME) ?? '0',
                $ruleset
            )
        );
    }

    private function getAbsolutePath(string $path): string
    {
        $isAbsolutePath = '' !== $path && (
            '/' === $path[0]
            || '\\' === $path[0]
            || 1 === preg_match('#^[a-zA-Z]:\\\\#', $path)
        );

        return $isAbsolutePath ? $path : $this->workingDir.\DIRECTORY_SEPARATOR.$path;
    }
}
