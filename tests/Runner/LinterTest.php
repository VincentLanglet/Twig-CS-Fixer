<?php

namespace TwigCsFixer\Tests\Runner;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\SyntaxError;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\Runner\Fixtures\BuggySniff;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;

/**
 * Test for Linter.
 */
class LinterTest extends TestCase
{
    /**
     * @return void
     */
    public function testUnreadableFilesAreReported(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);

        // Suppress the warning sent by `file_get_content` during the test.
        $oldErrorLevel = error_reporting(E_ALL ^ E_WARNING);
        $report = $linter->run([__DIR__.'/Fixtures/file_not_readable.twig'], $ruleset);
        error_reporting($oldErrorLevel);

        $messages = $report->getMessages();
        self::assertCount(1, $messages);
        self::assertSame('Unable to read file.', $messages[0]->getMessage());
        self::assertSame(
            sprintf('%s/Fixtures/file_not_readable.twig', __DIR__),
            $messages[0]->getFilename()
        );
    }

    /**
     * @return void
     */
    public function testInvalidFilesAreReported(): void
    {
        $env = $this->createStub(Environment::class);
        $env->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);

        $report = $linter->run([__DIR__.'/Fixtures/file.twig'], $ruleset);

        $messages = $report->getMessages();
        self::assertCount(1, $messages);
        self::assertSame('File is invalid: Error.', $messages[0]->getMessage());
        self::assertSame(
            sprintf('%s/Fixtures/file.twig', __DIR__),
            $messages[0]->getFilename()
        );
    }

    /**
     * @return void
     */
    public function testUntokenizableFilesAreReported(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);

        $report = $linter->run([__DIR__.'/Fixtures/file.twig'], $ruleset);

        $messages = $report->getMessages();
        self::assertCount(1, $messages);
        self::assertSame('Unable to tokenize file: Error.', $messages[0]->getMessage());
        self::assertSame(
            sprintf('%s/Fixtures/file.twig', __DIR__),
            $messages[0]->getFilename()
        );
    }

    /**
     * @return void
     */
    public function testBuggyRulesetCannotBeFixed(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();
        $ruleset->addSniff(new BuggySniff());

        $linter = new Linter($env, $tokenizer);

        self::expectExceptionMessage(sprintf('Cannot fix the file "%s/Fixtures/file.twig".', __DIR__));
        $linter->run([__DIR__.'/Fixtures/file.twig'], $ruleset, true);
    }
}
