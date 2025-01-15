<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Twig\Environment;
use Twig\Error\SyntaxError;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\Node\ForbiddenBlockRule;
use TwigCsFixer\Rules\Node\ForbiddenFilterRule;
use TwigCsFixer\Rules\Node\ForbiddenFunctionRule;
use TwigCsFixer\Rules\Whitespace\EmptyLinesRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Standard\TwigCsFixer;
use TwigCsFixer\Tests\FileTestCase;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;
use TwigCsFixer\Token\Tokens;

final class LinterTest extends FileTestCase
{
    public function testUnreadableFilesAreReported(): void
    {
        $fileNotReadablePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file_not_readable.twig');
        if ($this->getFilesystem()->exists($fileNotReadablePath)) {
            $this->getFilesystem()->remove($fileNotReadablePath);
        }

        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(true);
        // Ensure the second file is fixed and cached
        $cacheManager->expects(static::once())->method('setFile')->with($filePath);

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $report = $linter->run(
            [new \SplFileInfo($fileNotReadablePath), new \SplFileInfo($filePath)],
            $ruleset,
        );

        $messages = $report->getFileViolations($fileNotReadablePath);
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('Unable to read file.', $message->getMessage());
        static::assertSame(Violation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($fileNotReadablePath, $message->getFilename());

        static::assertCount(0, $report->getFileViolations($filePath));
    }

    public function testUntokenizableFilesAreReported(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');
        $filePath2 = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file2.twig');

        $env = new StubbedEnvironment();
        $tokenizer = static::createStub(TokenizerInterface::class);

        $call = 0;
        $tokenizer->method('tokenize')->willReturnCallback(
            static function () use (&$call): Tokens {
                /** @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/10513 */
                if (0 === $call) {
                    ++$call;
                    throw CannotTokenizeException::unknownError();
                }

                return new Tokens();
            }
        );
        $ruleset = new Ruleset();

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(true);
        // Ensure the second file is fixed and cached
        $cacheManager->expects(static::once())->method('setFile')->with($filePath2);

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $report = $linter->run(
            [new \SplFileInfo($filePath), new \SplFileInfo($filePath2)],
            $ruleset
        );

        $messages = $report->getFileViolations($filePath);
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('Unable to tokenize file: The template is invalid.', $message->getMessage());
        static::assertSame(Violation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

    public function testUserDeprecationAreReported(): void
    {
        $deprecations = 0;
        set_error_handler(static function () use (&$deprecations): bool {
            ++$deprecations;

            return true;
        }, \E_USER_DEPRECATED);

        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $env = new StubbedEnvironment();
        $tokenizer = static::createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturnCallback(static function (): Tokens {
            @trigger_error('Default');
            trigger_error('User Deprecation', \E_USER_DEPRECATED);

            return new Tokens();
        });
        $ruleset = new Ruleset();

        $linter = new Linter($env, $tokenizer);
        $report = $linter->run([new \SplFileInfo($filePath)], $ruleset);

        // Ensure the error handler is restored.
        @trigger_error('User Deprecation 2', \E_USER_DEPRECATED);
        static::assertSame(1, $deprecations);
        restore_error_handler();

        $messages = $report->getFileViolations($filePath);
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('User Deprecation', $message->getMessage());
        static::assertSame(Violation::LEVEL_NOTICE, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

    public function testFileIsModifiedWhenFixed(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $fixer = $this->createMock(FixerInterface::class);
        $fixer->expects(static::once())->method('fixFile')->willReturn('newContent');

        $linter = new Linter($env, $tokenizer);
        $linter->run([new \SplFileInfo($filePath)], $ruleset, $fixer);

        static::assertStringEqualsFile($filePath, 'newContent');
    }

    /**
     * @dataProvider buggyFixesAreReportedDataProvider
     */
    public function testBuggyFixesAreReported(
        CannotFixFileException|CannotTokenizeException $exception,
        string $expectedMessage,
    ): void {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');
        $filePath2 = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file2.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $call = 0;
        $fixer = static::createStub(FixerInterface::class);
        $fixer->method('fixFile')->willReturnCallback(
            static function () use (&$call, $exception): string {
                /** @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/10513 */
                if (0 === $call) {
                    ++$call;
                    throw $exception;
                }

                return '';
            }
        );

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(true);
        // Ensure the second file is fixed and cached
        $cacheManager->expects(static::once())->method('setFile')->with($filePath2);

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $report = $linter->run(
            [new \SplFileInfo($filePath), new \SplFileInfo($filePath2)],
            $ruleset,
            $fixer
        );

        $messages = $report->getFileViolations($filePath);
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertStringContainsString($expectedMessage, $message->getMessage());
        static::assertSame(Violation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
    }

    /**
     * @return iterable<array-key, array{CannotFixFileException|CannotTokenizeException, string}>
     */
    public static function buggyFixesAreReportedDataProvider(): iterable
    {
        yield [CannotFixFileException::infiniteLoop(), 'Unable to fix file'];
        yield [CannotTokenizeException::unknownError(), 'Unable to tokenize file'];
    }

    public function testFileIsSkippedIfCached(): void
    {
        $env = new StubbedEnvironment();
        $ruleset = new Ruleset();

        $tokenizer = $this->createMock(TokenizerInterface::class);
        $tokenizer->expects(static::never())->method('tokenize');

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(false);
        $cacheManager->expects(static::never())->method('setFile');

        $fixer = $this->createMock(FixerInterface::class);
        $fixer->expects(static::never())->method('fixFile');

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $linter->run([new \SplFileInfo(__DIR__.'/Fixtures/Linter/file.twig')], $ruleset, $fixer);
    }

    public function testFileIsNotSkippedIfNotCached(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(true);
        $cacheManager->expects(static::once())->method('setFile');

        $fixer = $this->createMock(FixerInterface::class);
        $fixer->expects(static::once())->method('fixFile');

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $linter->run([new \SplFileInfo($filePath)], $ruleset, $fixer);
    }

    public function testFileIsNotCachedWhenReportHasErrors(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();
        $ruleset->addStandard(new TwigCsFixer());

        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheManager->method('needFixing')->willReturn(true);
        $cacheManager->expects(static::never())->method('setFile');

        $linter = new Linter($env, $tokenizer, $cacheManager);
        $linter->run([new \SplFileInfo($filePath)], $ruleset);
    }

    public function testViolationsFromNodeVisitorRule(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/node_visitor.twig');

        $ruleset = new Ruleset();
        $ruleset->addRule(new EmptyLinesRule()); // Will be fixed before visitors
        $ruleset->addRule(new ForbiddenFilterRule(['trans']));
        $ruleset->addRule(new ForbiddenBlockRule(['trans']));
        $ruleset->addRule(new ForbiddenFunctionRule(['t']));

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);

        $report = $linter->run([new \SplFileInfo($filePath)], $ruleset, new Fixer($tokenizer));

        $messages = $report->getFileViolations($filePath);
        static::assertCount(4, $messages);

        $message = $messages[0];
        static::assertSame('Filter "trans" is not allowed.', $message->getMessage());
        static::assertSame(Violation::LEVEL_ERROR, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
        static::assertSame(3, $message->getLine());

        $message = $messages[1];
        static::assertSame('Filter "trans" is not allowed.', $message->getMessage());
        static::assertSame(Violation::LEVEL_ERROR, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
        static::assertSame(7, $message->getLine());

        $message = $messages[2];
        static::assertSame('Block "trans" is not allowed.', $message->getMessage());
        static::assertSame(Violation::LEVEL_ERROR, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
        static::assertSame(11, $message->getLine());

        $message = $messages[3];
        static::assertSame('Function "t" is not allowed.', $message->getMessage());
        static::assertSame(Violation::LEVEL_ERROR, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
        static::assertSame(13, $message->getLine());
    }

    public function testNodeVisitorWithInvalidFiles(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');
        $filePath2 = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file2.twig');

        $ruleset = new Ruleset();
        $ruleset->addRule(new EmptyLinesRule());
        $ruleset->addRule(new ForbiddenFilterRule(['trans']));
        $ruleset->addRule(new ForbiddenBlockRule(['trans']));
        $ruleset->addRule(new ForbiddenFunctionRule(['t']));

        $env = static::createStub(Environment::class);
        $env->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $tokenizer = static::createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturn(new Tokens());

        $linter = new Linter($env, $tokenizer);
        $report = $linter->run([new \SplFileInfo($filePath), new \SplFileInfo($filePath2)], $ruleset);

        $messages = $report->getFileViolations($filePath);
        static::assertCount(1, $messages);

        $message = $messages[0];
        static::assertSame('File is invalid: Error.', $message->getMessage());
        static::assertSame(Violation::LEVEL_FATAL, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());

        // We still validate other files
        $messages = $report->getFileViolations($filePath2);
        static::assertCount(1, $messages);
    }

    public function testNodeVisitorWithBuggyFixer(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');

        $ruleset = new Ruleset();
        $ruleset->addRule(new EmptyLinesRule());
        $ruleset->addRule(new ForbiddenFilterRule(['trans']));
        $ruleset->addRule(new ForbiddenBlockRule(['trans']));
        $ruleset->addRule(new ForbiddenFunctionRule(['t']));

        $env = new StubbedEnvironment();
        $tokenizer = static::createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturn(new Tokens());
        $linter = new Linter($env, $tokenizer);

        $fixer = static::createStub(FixerInterface::class);
        $fixer->method('fixFile')->willReturn('{{');
        $report = $linter->run([new \SplFileInfo($filePath)], $ruleset, $fixer);

        $messages = $report->getFileViolations($filePath);
        static::assertCount(1, $messages);

        if (InstalledVersions::satisfies(new VersionParser(), 'twig/twig', '>=3.15.0')) {
            static::assertSame('File is invalid: Unexpected end of template.', $messages[0]->getMessage());
        } else {
            static::assertSame('File is invalid: Unexpected token "end of template" of value "".', $messages[0]->getMessage());
        }
    }
}
