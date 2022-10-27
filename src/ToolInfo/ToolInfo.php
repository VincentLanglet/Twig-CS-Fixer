<?php

declare(strict_types=1);

namespace TwigCsFixer\ToolInfo;

/**
 * Obtain information about using version of tool.
 */
final class ToolInfo implements ToolInfoInterface
{
    public const COMPOSER_PACKAGE_NAME = 'k10r/twig-cs-fixer';

    private ?array $composerInstallationDetails = [];

    public function getComposerInstallationDetails(): array
    {
        if (null === $this->composerInstallationDetails) {
            $composerInstalled = json_decode(file_get_contents($this->getComposerInstalledFile()), true);

            $packages = $composerInstalled['packages'] ?? $composerInstalled;

            foreach ($packages as $package) {
                if (self::COMPOSER_PACKAGE_NAME === $package['name']) {
                    $this->composerInstallationDetails = $package;

                    break;
                }
            }
        }

        return $this->composerInstallationDetails;
    }

    public function getVersion(): string
    {
        $package = $this->getComposerInstallationDetails();

        $versionSuffix = '';

        if (isset($package['dist']['reference'])) {
            $versionSuffix = '#'.$package['dist']['reference'];
        }

        return $package['version'].$versionSuffix;
    }

    private function getComposerInstalledFile(): string
    {
        return __DIR__.'/../../../composer/installed.json';
    }
}
