<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Composer\InstalledVersions;
use Symfony\Component\Finder\Finder;
use TwigCsFixer\Cache\FileHandler\CacheFileHandler;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Cache\Manager\FileCacheManager;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Console\Application;
use TwigCsFixer\Exception\CannotResolveConfigException;
use TwigCsFixer\File\FileHelper;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Ruleset\Ruleset;

/**
 * Resolve config from `.twig-cs-fixer.php` if provided.
 */
final class ConfigResolver
{
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
        bool $disableCache = false,
    ): Config {
        $config = $this->getConfig($configPath);
        $config->setFinder($this->resolveFinder($config->getFinder(), $paths));

        // Override ruleset with config
        $config->getRuleset()->allowNonFixableRules($config->areNonFixableRulesAllowed());

        if ($disableCache) {
            $config->setCacheFile(null);
            $config->setCacheManager(null);
        } else {
            $config->setCacheManager($this->resolveCacheManager(
                $config->getCacheManager(),
                $config->getCacheFile(),
                $config->getRuleset(),
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
            return $this->getConfigFromPath(FileHelper::getAbsolutePath($configPath, $this->workingDir));
        }

        $defaultPath = FileHelper::getAbsolutePath(Config::DEFAULT_PATH, $this->workingDir);
        if (file_exists($defaultPath)) {
            return $this->getConfigFromPath($defaultPath);
        }

        $defaultDistPath = FileHelper::getAbsolutePath(Config::DEFAULT_DIST_PATH, $this->workingDir);
        if (file_exists($defaultDistPath)) {
            return $this->getConfigFromPath($defaultDistPath);
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
        } catch (\LogicException) {
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
        Ruleset $ruleset,
    ): ?CacheManagerInterface {
        if (null !== $cacheManager) {
            return $cacheManager;
        }

        if (null === $cacheFile) {
            return null;
        }

        return new FileCacheManager(
            new CacheFileHandler(FileHelper::getAbsolutePath($cacheFile, $this->workingDir)),
            Signature::fromRuleset(
                \PHP_VERSION,
                InstalledVersions::getReference(Application::PACKAGE_NAME) ?? '0',
                $ruleset,
            )
        );
    }
}
