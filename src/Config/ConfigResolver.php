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
            $configPath = 0 === mb_strpos($configPath, '/') ? $configPath : $this->workingDir.'/'.$configPath;

            return $this->getConfigFromPath($configPath);
        }

        if (file_exists($this->workingDir.'/.twig-cs-fixer.php')) {
            return $this->getConfigFromPath($this->workingDir.'/.twig-cs-fixer.php');
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
}
