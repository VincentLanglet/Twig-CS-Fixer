<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Sniff\AbstractSniff;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;

final class FixerTest extends TestCase
{
    public function testInvalidFile(): void
    {
        $exception = CannotTokenizeException::unknownError();

        $tokenizer = self::createStub(TokenizerInterface::class);
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

                // True for change set
                $fixer->beginChangeSet();
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'b'));
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'c'));
                $fixer->endChangeSet();

                // False if you replace multiple times the same token
                TestCase::assertFalse($fixer->replaceToken($tokenPosition, 'd'));

                // Still true for change set
                $fixer->beginChangeSet();
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'e'));
                TestCase::assertTrue($fixer->replaceToken($tokenPosition, 'f'));
                $fixer->endChangeSet();
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
            protected function process(int $tokenPosition, array $tokens): void
            {
                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                $fixer->replaceToken($tokenPosition, 'a');
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($tokenizer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop());
        $fixer->fixFile('', $ruleset);
    }

    public function testReplaceTokenIsDesignedAgainstConflict(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $sniff1 = new class () extends AbstractSniff {
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

                $fixer->replaceToken($tokenPosition, 'sniff');
            }
        };
        $sniff2 = new class () extends AbstractSniff {
            private int $error = 0;

            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($tokenPosition > 0) {
                    return;
                }

                if (
                    str_starts_with($tokens[$tokenPosition]->getValue(), 'test')
                    || is_numeric($tokens[$tokenPosition + 2]->getValue())
                    || is_numeric($tokens[$tokenPosition + 4]->getValue())
                ) {
                    return;
                }

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                $this->error++;
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeSet();
                $fixer->replaceToken($tokenPosition + 2, (string) $this->error);
                // Order matter, to check we revert the previous change
                $fixer->replaceToken($tokenPosition, 'test');
                // And to check we're not applying the next change
                $fixer->replaceToken($tokenPosition + 4, (string) $this->error);
                $fixer->endChangeSet();
            }
        };
        $sniff3 = new class () extends AbstractSniff {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenPosition, array $tokens): void
            {
                if ($this->isAlreadyExecuted || 'sniff' !== $tokens[$tokenPosition]->getValue()) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens[$tokenPosition]);
                if (null === $fixer) {
                    return;
                }

                // On the first execution, a conflict is created by sniff 1 and 2
                // So the fixer won't try to fix anything else
                TestCase::assertFalse($fixer->replaceToken($tokenPosition, 'b'));
                $fixer->beginChangeSet();
                TestCase::assertFalse($fixer->replaceToken($tokenPosition, 'b'));
                $fixer->endChangeSet();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff1);
        $ruleset->addSniff($sniff2);
        $ruleset->addSniff($sniff3);

        $fixer = new Fixer($tokenizer);

        static::assertSame('test 2 2', $fixer->fixFile('test test test', $ruleset));
    }

    /**
     * @dataProvider addContentMethodsDataProvider
     */
    public function testAddContentMethods(string $content, string $expected): void
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

                $fixer->beginChangeSet();
                TestCase::assertTrue($fixer->addContent($tokenPosition, 'a'));
                TestCase::assertTrue($fixer->addContentBefore($tokenPosition, 'b'));
                TestCase::assertTrue($fixer->addNewline($tokenPosition));
                TestCase::assertTrue($fixer->addNewlineBefore($tokenPosition));
                $fixer->endChangeSet();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($tokenizer);

        static::assertSame($expected, $fixer->fixFile($content, $ruleset));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function addContentMethodsDataProvider(): iterable
    {
        yield ['', "\nba\n"];
        yield ['foo', "\nbfooa\n"];
        yield ["\n", "\nb\na\n"];
        yield ["\r", "\rb\ra\r"];
    }

    public function testBeginChangeSetException(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());
        $fixer = new Fixer($tokenizer);

        $fixer->beginChangeSet();
        $this->expectException(BadMethodCallException::class);
        $fixer->beginChangeSet();
    }

    public function testEndChangeSetException(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());
        $fixer = new Fixer($tokenizer);

        $this->expectException(BadMethodCallException::class);
        $fixer->endChangeSet();
    }
}
