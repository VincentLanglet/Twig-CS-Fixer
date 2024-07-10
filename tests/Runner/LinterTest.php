<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\AbstractNodeVisitorRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Standard\TwigCsFixer;
use TwigCsFixer\Tests\FileTestCase;
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

    public function testInvalidFilesAreReported(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');
        $filePath2 = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file2.twig');

        $env = self::createStub(Environment::class);
        $env->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $tokenizer = self::createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

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

    public function testUntokenizableFilesAreReported(): void
    {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');
        $filePath2 = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file2.twig');

        $env = new StubbedEnvironment();
        $tokenizer = self::createStub(TokenizerInterface::class);

        $call = 0;
        $tokenizer->method('tokenize')->willReturnCallback(
            static function () use (&$call): array {
                /** @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/10513 */
                if (0 === $call) {
                    ++$call;
                    throw CannotTokenizeException::unknownError();
                }

                return [[], []];
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
        $tokenizer = self::createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturnCallback(static function (): array {
            @trigger_error('Default');
            trigger_error('User Deprecation', \E_USER_DEPRECATED);

            return [[], []];
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
        string $expectedMessage
    ): void {
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file.twig');
        $filePath2 = $this->getTmpPath(__DIR__.'/Fixtures/Linter/file2.twig');

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();

        $call = 0;
        $fixer = self::createStub(FixerInterface::class);
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
        $filePath = $this->getTmpPath(__DIR__.'/Fixtures/Linter/forbidden_filter.twig');

        $nodeVisitorRule = new class(['trans']) extends AbstractNodeVisitorRule {
            /**
             * @param list<string> $forbidden
             */
            public function __construct(
                private array $forbidden,
            ) {
            }

            public function enterNode(Node $node, Environment $env): Node
            {
                if (!$node instanceof FilterExpression) {
                    return $node;
                }

                if (!\in_array($node->getNode('filter')->getAttribute('value'), $this->forbidden, true)) {
                    return $node;
                }

                $this->addError(
                    sprintf('Filter "%s" is not allowed.', $node->getNode('filter')->getAttribute('value')),
                    $node,
                );

                return $node;
            }
        };

        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $ruleset = new Ruleset();
        $ruleset->addRule($nodeVisitorRule);

        $linter = new Linter($env, $tokenizer);

        $report = $linter->run([new \SplFileInfo($filePath)], $ruleset);

        $messages = $report->getFileViolations($filePath);
        static::assertCount(2, $messages);

        $message = $messages[0];
        static::assertSame('Filter "trans" is not allowed.', $message->getMessage());
        static::assertSame(Violation::LEVEL_ERROR, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
        static::assertSame(2, $message->getLine());

        $message = $messages[1];
        static::assertSame('Filter "trans" is not allowed.', $message->getMessage());
        static::assertSame(Violation::LEVEL_ERROR, $message->getLevel());
        static::assertSame($filePath, $message->getFilename());
        static::assertSame(4, $message->getLine());
    }
}
