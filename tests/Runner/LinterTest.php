<?php

namespace TwigCsFixer\Tests\Runner;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\SyntaxError;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\Runner\Fixtures\BuggySniff;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;

use function error_reporting;
use function sprintf;
use function trigger_error;

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
        $filePath = __DIR__.'/Fixtures/file_not_readable.twig';

        // Suppress the warning sent by `file_get_content` during the test.
        $oldErrorLevel = error_reporting(E_ALL ^ E_WARNING);
        $report = $linter->run([new SplFileInfo($filePath)], $ruleset, false);
        error_reporting($oldErrorLevel);

        $messagesByFiles = $report->getMessagesByFiles();
        self::assertCount(1, $messagesByFiles);
        self::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        self::assertCount(1, $messages);

        $message = $messages[0];
        self::assertSame('Unable to read file.', $message->getMessage());
        self::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        self::assertSame($filePath, $message->getFilename());
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
        $filePath = __DIR__.'/Fixtures/file.twig';

        $report = $linter->run([new SplFileInfo($filePath)], $ruleset, false);

        $messagesByFiles = $report->getMessagesByFiles();
        self::assertCount(1, $messagesByFiles);
        self::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        self::assertCount(1, $messages);

        $message = $messages[0];
        self::assertSame('File is invalid: Error.', $message->getMessage());
        self::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        self::assertSame($filePath, $message->getFilename());
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
        $filePath = __DIR__.'/Fixtures/file.twig';

        $report = $linter->run([new SplFileInfo($filePath)], $ruleset, false);

        $messagesByFiles = $report->getMessagesByFiles();
        self::assertCount(1, $messagesByFiles);
        self::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        self::assertCount(1, $messages);

        $message = $messages[0];
        self::assertSame('Unable to tokenize file: Error.', $message->getMessage());
        self::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        self::assertSame($filePath, $message->getFilename());
    }

    /**
     * @return void
     */
    public function testUserDeprecationAreReported(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturnCallback(static function (): array {
            @trigger_error('Default');
            @trigger_error('User Deprecation', \E_USER_DEPRECATED);

            return [];
        });
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $filePath = __DIR__.'/Fixtures/file.twig';

        $report = $linter->run([new SplFileInfo($filePath)], $ruleset, false);

        $messagesByFiles = $report->getMessagesByFiles();
        self::assertCount(1, $messagesByFiles);
        self::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        self::assertCount(1, $messages);

        $message = $messages[0];
        self::assertSame('User Deprecation', $message->getMessage());
        self::assertSame(SniffViolation::LEVEL_NOTICE, $message->getLevel());
        self::assertSame($filePath, $message->getFilename());
    }

    /**
     * @return void
     */
    public function testEmptyRulesetCanBeFixed(): void
    {
        self::expectNotToPerformAssertions();

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $linter->run([new SplFileInfo(__DIR__.'/Fixtures/file.twig')], $ruleset, true);
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
        $linter->run([new SplFileInfo(__DIR__.'/Fixtures/file.twig')], $ruleset, true);
    }
}
