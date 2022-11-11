<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TwigCsFixer\Command\TwigCsFixerCommand;
use TwigCsFixer\Config\Config;

final class TwigCsFixerCommandTest extends TestCase
{
    public function testExecuteWithPaths(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures'],
        ]);

        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithConfig(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths'    => [__DIR__.'/Fixtures'],
            '--config' => __DIR__.'/Fixtures/.twig-cs-fixer.php',
        ]);

        static::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithSuccess(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures/file.twig'],
        ]);

        static::assertStringContainsString(
            '[OK] Files linted: 1, notices: 0, warnings: 0, errors: 0',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithOptionFix(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures/file.twig'],
            '--fix' => true,
        ]);

        static::assertStringContainsString(
            '[OK] Files linted: 1, notices: 0, warnings: 0, errors: 0',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithError(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths'    => [__DIR__.'/Fixtures'],
            '--config' => __DIR__.'/Fixtures/.config-not-found.php',
        ]);

        static::assertStringStartsWith('Error: ', $commandTester->getDisplay());
        static::assertSame(Command::INVALID, $commandTester->getStatusCode());
    }

    public function testExecuteWithCacheByDefault(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);

        // Run two times to be sure to generate the cache.
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures/file.twig'],
        ]);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures/file.twig'],
        ]);

        static::assertStringContainsString(
            sprintf('Using cache file "%s".', Config::DEFAULT_CACHE_PATH),
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithCacheDisabled(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);

        // Run two times to be sure to generate the cache if we were using one.
        $commandTester->execute([
            'paths'      => [__DIR__.'/Fixtures/file.twig'],
            '--no-cache' => true,
        ]);
        $commandTester->execute([
            'paths'      => [__DIR__.'/Fixtures/file.twig'],
            '--no-cache' => true,
        ]);

        static::assertStringNotContainsString(
            'Using cache file',
            $commandTester->getDisplay()
        );
        static::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
