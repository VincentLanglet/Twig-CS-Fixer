<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\SyntaxError;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\Runner\Fixtures\BuggySniff;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;

class LinterTest extends TestCase
{
    public function testUnreadableFilesAreReported(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $filePath = __DIR__.'/Fixtures/file_not_readable.twig';

        // Suppress the warning sent by `file_get_content` during the test.
        $oldErrorLevel = error_reporting(\E_ALL ^ \E_WARNING);
        $report = $linter->run([new SplFileInfo($filePath)], $ruleset, false);
        error_reporting($oldErrorLevel);

        $messagesByFiles = $report->getMessagesByFiles();
        static::assertCount(1, $messagesByFiles);
        static::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('Unable to read file.', $message->getMessage());
        static::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

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
        static::assertCount(1, $messagesByFiles);
        static::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('File is invalid: Error.', $message->getMessage());
        static::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

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
        static::assertCount(1, $messagesByFiles);
        static::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('Unable to tokenize file: Error.', $message->getMessage());
        static::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

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
        static::assertCount(1, $messagesByFiles);
        static::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('User Deprecation', $message->getMessage());
        static::assertSame(SniffViolation::LEVEL_NOTICE, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

    public function testEmptyRulesetCanBeFixed(): void
    {
        self::expectNotToPerformAssertions();

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $linter->run([new SplFileInfo(__DIR__.'/Fixtures/file.twig')], $ruleset, true);
    }

    public function testBuggyRulesetCannotBeFixed(): void
    {
        // Avoid mutation-testing to modify the file
        $file = __DIR__.'/Fixtures/file.twig';
        $tmpFile = sys_get_temp_dir().'/file.twig';
        $copySuccessful = copy($file, $tmpFile);
        static::assertTrue($copySuccessful);

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();
        $ruleset->addSniff(new BuggySniff());

        $linter = new Linter($env, $tokenizer);

        self::expectExceptionMessage(sprintf('Cannot fix file "%s".', $tmpFile));
        $linter->run([new SplFileInfo($tmpFile)], $ruleset, true);
    }

    public function testFileIsSkippedIfCached(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = $this->createMock(TokenizerInterface::class);
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer, $cacheManager);

        $cacheManager->method('needFixing')->willReturn(false);
        $cacheManager->expects(static::never())->method('setFile');
        $tokenizer->expects(static::never())->method('tokenize');
        $linter->run([new SplFileInfo(__DIR__.'/Fixtures/file.twig')], $ruleset, true);
    }

    public function testFileIsNotSkippedIfNotCached(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer, $cacheManager);

        $cacheManager->method('needFixing')->willReturn(true);
        $cacheManager->expects(static::once())->method('setFile');
        $linter->run([new SplFileInfo(__DIR__.'/Fixtures/file.twig')], $ruleset, true);
    }
}
