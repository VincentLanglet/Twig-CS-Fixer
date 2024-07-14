<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Rules\AbstractFixableRule;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;
use TwigCsFixer\Token\Tokens;

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
            new Tokens([
                new Token(Token::EOF_TYPE, 0, 0, 'TwigCsFixer'),
            ]),
            [],
        ]);

        $ruleset = new Ruleset();
        $fixer = new Fixer($tokenizer);
        $fixer->fixFile('', $ruleset);
    }

    public function testReplaceToken(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $rule = new class() extends AbstractFixableRule {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                if ($this->isAlreadyExecuted) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                if (null === $fixer) {
                    return;
                }

                TestCase::assertTrue($fixer->replaceToken($tokenIndex, 'a'));

                // True for change set
                $fixer->beginChangeSet();
                TestCase::assertTrue($fixer->replaceToken($tokenIndex, 'b'));
                TestCase::assertTrue($fixer->replaceToken($tokenIndex, 'c'));
                $fixer->endChangeSet();

                // False if you replace multiple times the same token
                TestCase::assertFalse($fixer->replaceToken($tokenIndex, 'd'));

                // Still true for change set
                $fixer->beginChangeSet();
                TestCase::assertTrue($fixer->replaceToken($tokenIndex, 'e'));
                TestCase::assertTrue($fixer->replaceToken($tokenIndex, 'f'));
                $fixer->endChangeSet();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addRule($rule);

        $fixer = new Fixer($tokenizer);

        static::assertSame('a', $fixer->fixFile('', $ruleset));
    }

    public function testReplaceTokenIsDesignedAgainstInfiniteLoop(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $rule = new class() extends AbstractFixableRule {
            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                if (null === $fixer) {
                    return;
                }

                $fixer->replaceToken($tokenIndex, 'a');
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addRule($rule);

        $fixer = new Fixer($tokenizer);

        $this->expectExceptionObject(CannotFixFileException::infiniteLoop());
        $fixer->fixFile('', $ruleset);
    }

    public function testReplaceTokenIsDesignedAgainstConflict(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $rule1 = new class() extends AbstractFixableRule {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                if ($this->isAlreadyExecuted) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                if (null === $fixer) {
                    return;
                }

                $fixer->replaceToken($tokenIndex, 'rule');
            }
        };
        $rule2 = new class() extends AbstractFixableRule {
            private int $error = 0;

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                if ($tokenIndex > 0) {
                    return;
                }

                if (
                    str_starts_with($tokens->get($tokenIndex)->getValue(), 'test')
                    || is_numeric($tokens->get($tokenIndex + 2)->getValue())
                    || is_numeric($tokens->get($tokenIndex + 4)->getValue())
                ) {
                    return;
                }

                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                ++$this->error;
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeSet();
                $fixer->replaceToken($tokenIndex + 2, (string) $this->error);
                // Order matter, to check we revert the previous change
                $fixer->replaceToken($tokenIndex, 'test');
                // And to check we're not applying the next change
                $fixer->replaceToken($tokenIndex + 4, (string) $this->error);
                $fixer->endChangeSet();
            }
        };
        $rule3 = new class() extends AbstractFixableRule {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                if ($this->isAlreadyExecuted || 'rule' !== $tokens->get($tokenIndex)->getValue()) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                if (null === $fixer) {
                    return;
                }

                // On the first execution, a conflict is created by rule 1 and 2
                // So the fixer won't try to fix anything else
                TestCase::assertFalse($fixer->replaceToken($tokenIndex, 'b'));
                $fixer->beginChangeSet();
                TestCase::assertFalse($fixer->replaceToken($tokenIndex, 'b'));
                $fixer->endChangeSet();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addRule($rule1);
        $ruleset->addRule($rule2);
        $ruleset->addRule($rule3);

        $fixer = new Fixer($tokenizer);

        static::assertSame('test 2 2', $fixer->fixFile('test test test', $ruleset));
    }

    public function testIgnoredViolations(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $rule = new class() extends AbstractFixableRule {
            public function getShortName(): string
            {
                return 'Rule';
            }

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                $fixer = $this->addFixableWarning('Error', $tokens->get($tokenIndex));
                if (null !== $fixer) {
                    $fixer->replaceToken($tokenIndex, 'a');
                }

                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                if (null !== $fixer) {
                    $fixer->replaceToken($tokenIndex, 'b');
                }
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addRule($rule);

        $fixer = new Fixer($tokenizer);

        $content = '{# twig-cs-fixer-disable Rule #}';
        // The rule should produce an infinite loop but the comment disable it
        static::assertSame($content, $fixer->fixFile($content, $ruleset));
    }

    /**
     * @dataProvider addContentMethodsDataProvider
     */
    public function testAddContentMethods(string $content, string $expected): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $rule = new class() extends AbstractFixableRule {
            private bool $isAlreadyExecuted = false;

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                if ($this->isAlreadyExecuted) {
                    return;
                }
                $this->isAlreadyExecuted = true;

                $fixer = $this->addFixableError('Error', $tokens->get($tokenIndex));
                if (null === $fixer) {
                    return;
                }

                $fixer->beginChangeSet();
                TestCase::assertTrue($fixer->addContent($tokenIndex, 'a'));
                TestCase::assertTrue($fixer->addContentBefore($tokenIndex, 'b'));
                TestCase::assertTrue($fixer->addNewline($tokenIndex));
                TestCase::assertTrue($fixer->addNewlineBefore($tokenIndex));
                $fixer->endChangeSet();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addRule($rule);

        $fixer = new Fixer($tokenizer);

        static::assertSame($expected, $fixer->fixFile($content, $ruleset));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function addContentMethodsDataProvider(): iterable
    {
        yield ['', \PHP_EOL.'ba'.\PHP_EOL];
        yield ['foo', \PHP_EOL.'bfooa'.\PHP_EOL];
        yield ["\n", "\nb\na\n"];
        yield ["\r", "\rb\ra\r"];
    }

    public function testBeginChangeSetException(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());
        $fixer = new Fixer($tokenizer);

        $fixer->beginChangeSet();
        $this->expectException(\BadMethodCallException::class);
        $fixer->beginChangeSet();
    }

    public function testEndChangeSetException(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());
        $fixer = new Fixer($tokenizer);

        $this->expectException(\BadMethodCallException::class);
        $fixer->endChangeSet();
    }

    public function testNonFixableRulesAreSkipped(): void
    {
        $tokenizer = new Tokenizer(new StubbedEnvironment());

        $rule = new class() extends AbstractRule {
            protected function process(int $tokenIndex, Tokens $tokens): void
            {
                throw new \LogicException('Should be skipped');
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addRule($rule);

        $fixer = new Fixer($tokenizer);

        $content = '';
        static::assertSame($content, $fixer->fixFile($content, $ruleset));
    }
}
