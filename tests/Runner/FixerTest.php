<?php

declare(strict_types=1);

namespace Runner;

use PHPUnit\Framework\TestCase;
use Twig\Error\SyntaxError;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Token\TokenizerInterface;

/**
 * Test for Fixer.
 */
class FixerTest extends TestCase
{
    public function testUnreadableFile(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $fixer = new Fixer($ruleset, $tokenizer);

        // Suppress the warning sent by `file_get_content` during the test.
        $oldErrorLevel = error_reporting(\E_ALL ^ \E_WARNING);
        $success = $fixer->fixFile(__DIR__.'/Fixtures/file_not_readable.twig');
        error_reporting($oldErrorLevel);

        self::assertFalse($success);
    }

    public function testInvalidFile(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $ruleset = new Ruleset();

        $fixer = new Fixer($ruleset, $tokenizer);
        self::assertFalse($fixer->fixFile(__DIR__.'/Fixtures/file.twig'));
    }
}
