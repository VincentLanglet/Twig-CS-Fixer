<?php

namespace TwigCsFixer\Tests\Console;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Console\Application;

final class ApplicationTest extends TestCase
{
    public function testVersion(): void
    {
        $app = new Application();
        static::assertSame(Application::APPLICATION_NAME, $app->getName());
        static::assertStringStartsWith('dev-', $app->getVersion());
    }
}
