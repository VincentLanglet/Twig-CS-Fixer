<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Console;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Console\Application;

final class ApplicationTest extends TestCase
{
    public function testVersion(): void
    {
        $app = new Application();
        static::assertSame(Application::APPLICATION_NAME, $app->getName());
        static::assertMatchesRegularExpression('/^dev-.+@.{7}$/', $app->getVersion());
    }

    public function testNotInstalledLib(): void
    {
        $app = new Application('Foo', 'foo');
        static::assertSame('Foo', $app->getName());
        static::assertSame('UNKNOWN', $app->getVersion());
    }

    public function testLibWithoutVersion(): void
    {
        $app = new Application('Psalm', 'psalm/psalm');
        static::assertSame('Psalm', $app->getName());
        static::assertSame('dev', $app->getVersion());
    }
}
