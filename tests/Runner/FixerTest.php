<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Sniff\AbstractSniff;
use TwigCsFixer\Tests\FileTestCase;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;
use Webmozart\Assert\Assert;

final class FixerTest extends FileTestCase
{
    public function testUnreadableFile(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $fixer = new Fixer($ruleset, $tokenizer);

        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/file_not_readable.twig');
        $this->expectExceptionObject(CannotFixFileException::fileNotReadable($file));
        $fixer->fixFile($file);
    }

    public function testInvalidFile(): void
    {
        $exception = CannotTokenizeException::unknownError();

        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willThrowException($exception);
        $ruleset = new Ruleset();

        $fixer = new Fixer($ruleset, $tokenizer);

        $this->expectExceptionObject($exception);
        $fixer->fixFile($this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig'));
    }

    public function testValidFile(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file);

        $tokenizer = $this->createMock(TokenizerInterface::class);
        $tokenizer->expects(static::once())->method('tokenize')->willReturn([
            new Token(Token::EOF_TYPE, 0, 0, $file),
        ]);

        $ruleset = new Ruleset();
        $fixer = new Fixer($ruleset, $tokenizer);
        $fixer->fixFile($file);
    }

    public function testReplaceToken(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file);

        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff = new class () extends AbstractSniff {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($this->isAlreadyExecuted) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'a'));

                // True for changeset
                $fixer->beginChangeset();
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'b'));
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'c'));
                $fixer->endChangeset();

                // False if you replace mutiple times the same token
                TestCase::assertFalse($fixer->replaceToken($tokenPosition, 'd'));

                // Still true for changeset
                $fixer->beginChangeset();
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'e'));
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'f'));
                $fixer->endChangeset();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff->enableFixer($fixer);
        $fixer->fixFile($file);

        static::assertSame('a', $fixer->getContents());
    }

    public function testReplaceTokenIsDesignedAgainstInfiniteLoop(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file);

        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff = new class () extends AbstractSniff {
            private int $executed = 0;

            protected function process(int $tokenPosition, array $tokens): void
            {
                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->replaceToken($tokenPosition, 'a');

                $fixer->beginChangeset();
                $fixer->replaceToken($tokenPosition, 'b');
                $fixer->endChangeset();

                $this->executed++;
            }

            public function getExecuted(): int
            {
                return $this->executed;
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff->enableFixer($fixer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop($file));
        $fixer->fixFile($file);

        static::assertSame('a', $fixer->getContents());
        static::assertSame(Fixer::MAX_FIXER_ITERATION, $sniff->getExecuted());
    }

    public function testReplaceTokenIsDesignedAgainstConflict(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file, 'test'); // This test require a non-empty file

        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff1 = new class () extends AbstractSniff {
            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($tokenPosition > 0) {
                    return;
                }

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeset();
                $fixer->replaceToken($tokenPosition, 'a');
                $fixer->endChangeset();
            }
        };
        $sniff2 = new class () extends AbstractSniff {
            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($tokenPosition > 0) {
                    return;
                }

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeset();
                $fixer->replaceToken($tokenPosition + 1, 'b');
                $fixer->replaceToken($tokenPosition, 'b');
                $fixer->endChangeset();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff1);
        $ruleset->addSniff($sniff2);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff1->enableFixer($fixer);
        $sniff2->enableFixer($fixer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop($file));
        try {
            $fixer->fixFile($file);
        } finally {
            // No change should be done (even if there is no conflict on token position 1)
            static::assertSame('test', file_get_contents($file));
        }
    }

    public function testReplaceTokenIsDesignedAgainstConflict2(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file, 'test'); // This test require a non-empty file

        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff1 = new class () extends AbstractSniff {
            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($tokenPosition > 0) {
                    return;
                }

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeset();
                $fixer->replaceToken($tokenPosition, 'a');
                $fixer->endChangeset();
            }
        };
        $sniff2 = new class () extends AbstractSniff {
            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($tokenPosition > 0) {
                    return;
                }

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeset();
                $fixer->replaceToken($tokenPosition + 1, 'b');
                $fixer->replaceToken($tokenPosition, 'b');
                $fixer->endChangeset();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff1);
        $ruleset->addSniff($sniff2);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff1->enableFixer($fixer);
        $sniff2->enableFixer($fixer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop($file));
        try {
            $fixer->fixFile($file);
        } finally {
            // No change should be done (even if there is no conflict on token position 1)
            static::assertSame('test', file_get_contents($file));
        }
    }

    public function testAddContentMethods(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file);

        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff = new class () extends AbstractSniff {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($this->isAlreadyExecuted) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                TestCase::assertTrue($fixer->addContent($tokenPosition, 'a'));
                TestCase::assertFalse($fixer->addContentBefore($tokenPosition, 'a'));
                TestCase::assertFalse($fixer->addNewline($tokenPosition));
                TestCase::assertFalse($fixer->addNewlineBefore($tokenPosition));

                // True for changeset
                $fixer->beginChangeset();
                TestCase::assertTrue($fixer->addContent($tokenPosition, 'a'));
                TestCase::assertTrue($fixer->addContentBefore($tokenPosition, 'b'));
                TestCase::assertTrue($fixer->addNewline($tokenPosition));
                TestCase::assertTrue($fixer->addNewlineBefore($tokenPosition));
                $fixer->endChangeset();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff->enableFixer($fixer);
        $fixer->fixFile($file);

        static::assertSame('a', $fixer->getContents());
    }

    public function testAddContentMethodsWithChangeset(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/Fixer/emptyFile.twig');
        $this->resetFile($file);

        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff = new class () extends AbstractSniff {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($this->isAlreadyExecuted) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeset();
                TestCase::assertTrue($fixer->addContent($tokenPosition, 'a'));
                TestCase::assertTrue($fixer->addContentBefore($tokenPosition, 'b'));
                TestCase::assertTrue($fixer->addNewline($tokenPosition));
                TestCase::assertTrue($fixer->addNewlineBefore($tokenPosition));
                $fixer->endChangeset();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff->enableFixer($fixer);
        $fixer->fixFile($file);

        static::assertSame("\nba\n", $fixer->getContents());
    }

    private function resetFile(string $path, string $content = ''): void
    {
        $success = file_put_contents($path, $content);
        Assert::notFalse($success);
    }
}
