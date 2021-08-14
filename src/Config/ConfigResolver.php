<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Exception;

/**
 * Resolve config from `.twig-cs-fixer.php` is provided
 */
class ConfigResolver
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
     * @param string|null $configPath
     *
     * @return Config
     *
     * @throws Exception
     */
    public function getConfig(?string $configPath = null): Config
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
