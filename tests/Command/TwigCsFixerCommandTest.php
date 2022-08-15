<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TwigCsFixer\Command\TwigCsFixerCommand;

final class TwigCsFixerCommandTest extends TestCase
{
    public function testExecuteWithPaths(): void
    {
        $command = new TwigCsFixerCommand();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures'],
        ]);

        self::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $commandTester->getDisplay()
        );
        self::assertSame(1, $commandTester->getStatusCode());
    }

    public function testExecuteWithConfig(): void
    {
        $command = new TwigCsFixerCommand();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths'    => [__DIR__.'/Fixtures'],
            '--config' => __DIR__.'/Fixtures/.twig-cs-fixer.php',
        ]);

        self::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $commandTester->getDisplay()
        );
        self::assertSame(1, $commandTester->getStatusCode());
    }

    public function testExecuteWithSuccess(): void
    {
        $command = new TwigCsFixerCommand();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures/file.twig'],
        ]);

        self::assertStringContainsString(
            '[OK] Files linted: 1, notices: 0, warnings: 0, errors: 0',
            $commandTester->getDisplay()
        );
        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithOptionFix(): void
    {
        $command = new TwigCsFixerCommand();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/Fixtures/file.twig'],
            '--fix' => true,
        ]);

        self::assertStringContainsString(
            '[OK] Files linted: 1, notices: 0, warnings: 0, errors: 0',
            $commandTester->getDisplay()
        );
        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithError(): void
    {
        $command = new TwigCsFixerCommand();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths'    => [__DIR__.'/Fixtures'],
            '--config' => __DIR__.'/Fixtures/.config-not-found.php',
        ]);

        self::assertStringStartsWith('Error: ', $commandTester->getDisplay());
        self::assertSame(1, $commandTester->getStatusCode());
    }
}
