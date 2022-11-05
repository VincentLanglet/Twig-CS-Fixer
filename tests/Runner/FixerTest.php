<?php

declare(strict_types=1);

namespace Runner;

use PHPUnit\Framework\TestCase;
use Twig\Error\SyntaxError;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Sniff\AbstractSniff;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;
use TwigCsFixer\Token\TokenizerInterface;

class FixerTest extends TestCase
{
    public function testUnreadableFile(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $ruleset = new Ruleset();

        $fixer = new Fixer($ruleset, $tokenizer);

        // Suppress the warning sent by `file_get_content` during the test.
        $oldErrorLevel = error_reporting(\E_ALL ^ \E_WARNING);
        $success = $fixer->fixFile(__DIR__.'/Fixtures/file_not_readable.twig');
        error_reporting($oldErrorLevel);

        static::assertFalse($success);
    }

    public function testInvalidFile(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willThrowException(new SyntaxError('Error.'));
        $ruleset = new Ruleset();

        $fixer = new Fixer($ruleset, $tokenizer);
        static::assertFalse($fixer->fixFile(__DIR__.'/Fixtures/file.twig'));
    }

    public function testReplaceToken(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturn([
            new Token(Token::EOF_TYPE, 0, 0, __DIR__.'/Fixtures/file.twig'),
        ]);

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
        static::assertTrue($fixer->fixFile(__DIR__.'/Fixtures/file.twig'));
    }

    public function testReplaceTokenIsDesignedAgainstInfiniteLoop(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturn([
            new Token(Token::EOF_TYPE, 0, 0, __DIR__.'/Fixtures/file.twig'),
        ]);

        $sniff = new class () extends AbstractSniff {
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
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff->enableFixer($fixer);
        static::assertFalse($fixer->fixFile(__DIR__.'/Fixtures/file.twig'));
    }

    public function testReplaceTokenIsDesignedAgainstConflict(): void
    {
        // Avoid mutation-testing to modify the file
        $file = __DIR__.'/Fixtures/file.twig';
        $tmpFile = sys_get_temp_dir().'/file.twig';
        $copySuccessful = copy($file, $tmpFile);
        static::assertTrue($copySuccessful);

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
        static::assertFalse($fixer->fixFile($tmpFile));

        // No change should be done (even if there is no conflict on token position 1)
        static::assertFileEquals($file, $tmpFile);
    }

    public function testAddContentMethods(): void
    {
        $tokenizer = $this->createStub(TokenizerInterface::class);
        $tokenizer->method('tokenize')->willReturn([
            new Token(Token::EOF_TYPE, 0, 0, __DIR__.'/Fixtures/file.twig'),
        ]);

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
                TestCase::assertTrue($fixer->addContentBefore($tokenPosition, 'a'));
                TestCase::assertTrue($fixer->addNewline($tokenPosition));
                TestCase::assertTrue($fixer->addNewlineBefore($tokenPosition));
                $fixer->endChangeset();
            }
        };

        $ruleset = new Ruleset();
        $ruleset->addSniff($sniff);

        $fixer = new Fixer($ruleset, $tokenizer);
        $sniff->enableFixer($fixer);
        static::assertTrue($fixer->fixFile(__DIR__.'/Fixtures/file.twig'));
    }
}
