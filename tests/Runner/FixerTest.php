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

final class FixerTest extends TestCase
{
    public function testInvalidFile(): void
    {
        $exception = CannotTokenizeException::unknownError();

        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willThrowException($exception);
        $ruleset = new Ruleset();

        $fixer = new Fixer($tokenizer);

        $this->expectExceptionObject($exception);
        $fixer->fixFile('', $ruleset);
    }

    public function testValidFile(): void
    {
        $tokenizer = $this->createMock(TokenizerInterface::class);
        $tokenizer->expects(static::once())->method('tokenize')->willReturn([
            new Token(Token::EOF_TYPE, 0, 0, 'TwigCsFixer'),
        ]);

        $ruleset = new Ruleset();
        $fixer = new Fixer($tokenizer);
        $fixer->fixFile('', $ruleset);
    }

    public function testReplaceToken(): void
    {
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

        $fixer = new Fixer($tokenizer);

        static::assertSame('a', $fixer->fixFile('', $ruleset));
    }

    public function testReplaceTokenIsDesignedAgainstInfiniteLoop(): void
    {
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

        $fixer = new Fixer($tokenizer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop());

        static::assertSame('a', $fixer->fixFile('', $ruleset));
        static::assertSame(Fixer::MAX_FIXER_ITERATION, $sniff->getExecuted());
    }

    public function testReplaceTokenIsDesignedAgainstConflict(): void
    {
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

        $fixer = new Fixer($tokenizer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop());
        $fixer->fixFile('test', $ruleset);
    }

    public function testReplaceTokenIsDesignedAgainstConflict2(): void
    {
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

        $fixer = new Fixer($tokenizer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop());
        $fixer->fixFile('test', $ruleset);
    }

    public function testAddContentMethods(): void
    {
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

        $fixer = new Fixer($tokenizer);

        static::assertSame('a', $fixer->fixFile('', $ruleset));
    }

    public function testAddContentMethodsWithChangeset(): void
    {
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

        $fixer = new Fixer($tokenizer);

        static::assertSame("\nba\n", $fixer->fixFile('', $ruleset));
    }
}
