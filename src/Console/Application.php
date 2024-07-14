<?php

declare(strict_types=1);

namespace TwigCsFixer\Console;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const APPLICATION_NAME = 'Twig-CS-Fixer';
    public const PACKAGE_NAME = 'vincentlanglet/twig-cs-fixer';

    /**
     * @throws \OutOfBoundsException
     */
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

    /**
     * @throws \OutOfBoundsException
     */
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

        throw new \OutOfBoundsException(sprintf('Package "%s" is not installed', self::PACKAGE_NAME));
    }
}
