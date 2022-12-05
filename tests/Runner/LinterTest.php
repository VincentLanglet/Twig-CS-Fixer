<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use SplFileInfo;
use Twig\Environment;
use Twig\Error\SyntaxError;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\FileTestCase;
use TwigCsFixer\Tests\Runner\Fixtures\Linter\BuggySniff;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;

final class LinterTest extends FileTestCase
{
    public function testUnreadableFilesAreReported(): void
    {
        $fileNotReadablePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file_not_readable.twig');
        if ($this->getFilesystem()->exists($fileNotReadablePath)) {
            $this->getFilesystem()->remove($fileNotReadablePath);
        }

        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(true);
        // Ensure the second file is fixed and cached
        $cacheManager->expects(static::once())->method('setFile')->with($filePath);

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();
        $linter = new Linter($env, $tokenizer, $cacheManager);

        $report = $linter->run(
            [new SplFileInfo($fileNotReadablePath), new SplFileInfo($filePath)],
            $ruleset,
        );

        $messagesByFiles = $report->getMessagesByFiles();
        static::assertCount(2, $messagesByFiles);
        static::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$fileNotReadablePath];
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('Unable to read file.', $message->getMessage());
        static::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($fileNotReadablePath, $message->getFilename());

        static::assertCount(0, $messagesByFiles[$filePath]);
    }

    public function testInvalidFilesAreReported(): void
    {
        $env = $this->createStub(Environment::class);
        $env->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $report = $linter->run([new SplFileInfo($filePath)], $ruleset);

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
        $tokenizer->method('tokenize')->willThrowException(CannotTokenizeException::unknownError());
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $report = $linter->run([new SplFileInfo($filePath)], $ruleset);

        $messagesByFiles = $report->getMessagesByFiles();
        static::assertCount(1, $messagesByFiles);
        static::assertArrayHasKey($filePath, $messagesByFiles);

        $messages = $messagesByFiles[$filePath];
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('Unable to tokenize file: The template is invalid.', $message->getMessage());
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
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $report = $linter->run([new SplFileInfo($filePath)], $ruleset);

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
        $fixer = new Fixer($tokenizer);
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $linter->run([new SplFileInfo($filePath)], $ruleset, $fixer);
    }

    public function testBuggyRulesetCannotBeFixed(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();
        $ruleset->addSniff(new BuggySniff());

        $linter = new Linter($env, $tokenizer);
        $fixer = new Fixer($tokenizer);

        $report = $linter->run([new SplFileInfo($file)], $ruleset, $fixer);

        $messagesByFiles = $report->getMessagesByFiles();
        static::assertCount(1, $messagesByFiles);
        static::assertArrayHasKey($file, $messagesByFiles);

        $messages = $messagesByFiles[$file];
        static::assertNotCount(0, $messages);

        $message = $messages[0];
        static::assertStringContainsString('Unable to fix file', $message->getMessage());
        static::assertSame(SniffViolation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($file, $message->getFilename());
    }

    public function testFileIsSkippedIfCached(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = $this->createMock(TokenizerInterface::class);
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $fixer = new Fixer($tokenizer);

        $cacheManager->method('needFixing')->willReturn(false);
        $cacheManager->expects(static::never())->method('setFile');
        $tokenizer->expects(static::never())->method('tokenize');
        $linter->run([new SplFileInfo(__DIR__.'/Fixtures/Linter/file.twig')], $ruleset, $fixer);
    }

    public function testFileIsNotSkippedIfNotCached(): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $fixer = new Fixer($tokenizer);
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $cacheManager->method('needFixing')->willReturn(true);
        $cacheManager->expects(static::once())->method('setFile');

        $linter->run([new SplFileInfo($filePath)], $ruleset, $fixer);
    }
}
