<?php

declare(strict_types=1);

namespace TwigCsFixer\Console;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const APPLICATION_NAME = 'Twig-CS-Fixer';
    public const PACKAGE_NAME = 'vincentlanglet/twig-cs-fixer';

    public function __construct()
    {
        parent::__construct(self::APPLICATION_NAME, $this->getPackageVersion());
    }

    private function getPackageVersion(): string
    {
        foreach (InstalledVersions::getAllRawData() as $installed) {
            if (!isset($installed['versions'][self::PACKAGE_NAME])) {
                continue;
            }

            $version = $installed['versions'][self::PACKAGE_NAME]['pretty_version']
                ?? $installed['versions'][self::PACKAGE_NAME]['version']
                ?? 'dev';

            $aliases = $installed['versions'][self::PACKAGE_NAME]['aliases'] ?? [];

            $reference = InstalledVersions::getReference(self::PACKAGE_NAME);
            if (null === $reference) {
                return $aliases[0] ?? $version;
            }

            return sprintf(
                '%s@%s',
                $aliases[0] ?? $version,
                substr($reference, 0, 7)
            );
        }

        return 'UNKNOWN';
    }
}
