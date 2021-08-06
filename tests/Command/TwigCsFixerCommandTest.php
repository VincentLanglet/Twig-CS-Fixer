<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TwigCsFixer\Command\TwigCsFixerCommand;

/**
 * Test of Tokenizer.
 */
final class TwigCsFixerCommandTest extends TestCase
{
    /**
     * @return void
     */
    public function testExecuteWithNoPaths(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertStringContainsString(
            '[SUCCESS] Files linted: 0, notices: 0, warnings: 0, errors: 0',
            $commandTester->getDisplay()
        );
        self::assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @return void
     */
    public function testExecuteWithPaths(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths' => [__DIR__.'/data'],
        ]);

        self::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 3',
            $commandTester->getDisplay()
        );
        self::assertSame(1, $commandTester->getStatusCode());
    }

    /**
     * @return void
     */
    public function testExecuteWithConfig(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths'    => [__DIR__.'/data'],
            '--config' => __DIR__.'/data/.twig-cs-fixer.php',
        ]);

        self::assertStringContainsString(
            '[ERROR] Files linted: 3, notices: 0, warnings: 0, errors: 1',
            $commandTester->getDisplay()
        );
        self::assertSame(1, $commandTester->getStatusCode());
    }

    /**
     * @return void
     */
    public function testExecuteWithError(): void
    {
        $command = new TwigCsFixerCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'paths'    => [__DIR__.'/data'],
            '--config' => __DIR__.'/data/.config-not-found.php',
        ]);

        self::assertStringStartsWith('Error: ', $commandTester->getDisplay());
        self::assertSame(1, $commandTester->getStatusCode());
    }
}
