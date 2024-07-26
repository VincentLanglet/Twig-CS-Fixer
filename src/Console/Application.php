<?php

declare(strict_types=1);

namespace TwigCsFixer\Console;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const APPLICATION_NAME = 'Twig-CS-Fixer';
    public const PACKAGE_NAME = 'vincentlanglet/twig-cs-fixer';

    public function __construct(string $name = self::APPLICATION_NAME, string $package = self::PACKAGE_NAME)
    {
        parent::__construct($name, $this->getPackageVersion($package));
    }

    private function getPackageVersion(string $package): string
    {
        foreach (InstalledVersions::getAllRawData() as $installed) {
            if (!isset($installed['versions'][$package])) {
                continue;
            }

            $version = $installed['versions'][$package]['pretty_version'] ?? 'dev';
            $reference = $installed['versions'][$package]['reference'] ?? null;
            if (null === $reference) {
                return $version;
            }

            return \sprintf('%s@%s', $version, substr($reference, 0, 7));
        }

        return 'UNKNOWN';
    }
}
