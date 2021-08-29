<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Exception;
use LogicException;
use Symfony\Component\Finder\Finder;
use TwigCsFixer\File\Finder as TwigCsFinder;

use function file_exists;
use function is_file;
use function preg_match;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * Resolve config from `.twig-cs-fixer.php` is provided
 */
final class ConfigResolver
{
    /**
     * @var string
     */
    private $workingDir;

    /**
     * @param string $workingDir
     *
     * @return void
     */
    public function __construct(string $workingDir)
    {
        $this->workingDir = $workingDir;
    }

    /**
     * @param string[]    $paths
     * @param string|null $configPath
     *
     * @return Config
     *
     * @throws Exception
     */
    public function resolveConfig(array $paths, ?string $configPath = null): Config
    {
        $config = $this->getConfig($configPath);
        $config->setFinder($this->resolveFinder($config->getFinder(), $paths));

        return $config;
    }

    /**
     * @param string|null $configPath
     *
     * @return Config
     *
     * @throws Exception
     */
    private function getConfig(?string $configPath = null): Config
    {
        if (null !== $configPath) {
            $configPath = $this->isAbsolutePath($configPath)
                ? $configPath
                : $this->workingDir.DIRECTORY_SEPARATOR.$configPath;

            return $this->getConfigFromPath($configPath);
        }

        if (file_exists($this->workingDir.DIRECTORY_SEPARATOR.'.twig-cs-fixer.php')) {
            return $this->getConfigFromPath($this->workingDir.DIRECTORY_SEPARATOR.'.twig-cs-fixer.php');
        }

        return new Config();
    }

    /**
     * @param string $configPath
     *
     * @return Config
     *
     * @throws Exception
     */
    private function getConfigFromPath(string $configPath): Config
    {
        if (!file_exists($configPath)) {
            throw new Exception(sprintf('Cannot find the config file "%s".', $configPath));
        }

        $config = require($configPath);
        if (!$config instanceof Config) {
            throw new Exception(sprintf('The config file must return a "%s" object.', Config::class));
        }

        return $config;
    }

    /**
     * @param Finder   $finder
     * @param string[] $paths
     *
     * @return Finder
     */
    private function resolveFinder(Finder $finder, array $paths): Finder
    {
        $nestedFinder = null;
        try {
            $nestedFinder = $finder->getIterator();
        } catch (LogicException $exception) {
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
                $directories[] = $path.DIRECTORY_SEPARATOR;
            }
        }

        if (null === $nestedFinder) {
            return $finder->in($directories)->append($files);
        }

        return TwigCsFinder::create()->in($directories)->append($files);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isAbsolutePath(string $path): bool
    {
        return '' !== $path && (
            '/' === $path[0]
            || '\\' === $path[0]
            || 1 === preg_match('#^[a-zA-Z]:\\\\#', $path)
        );
    }
}
