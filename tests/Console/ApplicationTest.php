<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Console;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Console\Application;

final class ApplicationTest extends TestCase
{
    public function testVersion(): void
    {
        $app = new Application();
        static::assertSame(Application::APPLICATION_NAME, $app->getName());

        $version = InstalledVersions::getPrettyVersion(Application::PACKAGE_NAME) ?? '';
        $ref = InstalledVersions::getReference(Application::PACKAGE_NAME) ?? '';
        static::assertSame($version.'@'.substr($ref, 0, 7), $app->getVersion());
    }

    public function testNotInstalledLib(): void
    {
        $app = new Application('Foo', 'foo');
        static::assertSame('Foo', $app->getName());
        static::assertSame('UNKNOWN', $app->getVersion());
    }

    public function testLibWithoutReference(): void
    {
        $app = new Application('NoReference', 'symfony/service-implementation');
        static::assertSame('NoReference', $app->getName());
        static::assertSame('dev', $app->getVersion());
    }
}
