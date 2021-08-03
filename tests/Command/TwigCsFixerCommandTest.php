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
    }
}
