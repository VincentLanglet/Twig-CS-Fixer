<?php

declare(strict_types=1);

namespace TwigCsFixer\Console;

use Composer\InstalledVersions;
use OutOfBoundsException;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const APPLICATION_NAME = 'Twig-CS-Fixer';
    public const PACKAGE_NAME = 'vincentlanglet/twig-cs-fixer';

    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        if ('UNKNOWN' === $name) {
            $name = self::APPLICATION_NAME;
        }
        if ('UNKNOWN' === $version) {
            $version = self::getPrettyVersion();
        }
        parent::__construct($name, $version);
    }

    public static function getPrettyVersion(): string
    {
        foreach (InstalledVersions::getAllRawData() as $installed) {
            if (!isset($installed['versions'][self::PACKAGE_NAME])) {
                continue;
            }

            $version = $installed['versions'][self::PACKAGE_NAME]['pretty_version']
                ?? $installed['versions'][self::PACKAGE_NAME]['version']
                ?? 'dev'
            ;

            $aliases = $installed['versions'][self::PACKAGE_NAME]['aliases'] ?? [];

            return sprintf(
                '%s@%s',
                $aliases[0] ?? $version,
                substr(InstalledVersions::getReference(self::PACKAGE_NAME), 0, 7)
            );
        }

        throw new OutOfBoundsException(sprintf('Package "%s" is not installed', self::PACKAGE_NAME));
    }
}
