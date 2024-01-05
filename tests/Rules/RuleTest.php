<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\Rules\Fixtures\FakeRule;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokenizer;

final class RuleTest extends TestCase
{
    public function testRuleWithReport(): void
    {
        $report = new Report([new SplFileInfo('fakeFile.html.twig')]);

        $rule = new class () extends AbstractRule {
            protected function process(int $tokenPosition, array $tokens): void
            {
                $token = $tokens[$tokenPosition];

                if (0 === $tokenPosition) {
                    $this->addWarning('Fake Warning', $token);
                    $this->addError('Fake Error', $token);
                    $this->addFixableWarning('Fake fixable warning', $token);
                    $this->addFixableError('Fake fixable error', $token);
                }
            }
        };

        $rule->lintFile([new Token(Token::EOF_TYPE, 0, 0, 'fakeFile.html.twig')], $report);

        static::assertSame(2, $report->getTotalWarnings());
        static::assertSame(2, $report->getTotalErrors());
    }

    public function testRuleName(): void
    {
        $rule = new FakeRule();
        static::assertSame(FakeRule::class, $rule->getName());
        static::assertSame('Fake', $rule->getShortName());
    }

    public function testRuleWithReport2(): void
    {
        $report = new Report([new SplFileInfo('fakeFile.html.twig')]);

        $rule = new class () extends AbstractRule {
            protected function process(int $tokenPosition, array $tokens): void
            {
                $token = $tokens[$tokenPosition];

                if (0 === $tokenPosition) {
                    // Ensure calling findPrevious on first token doesn't fail
                    $previousEol = $this->findPrevious(Token::TEXT_TYPE, $tokens, $tokenPosition - 1);
                    if (false !== $previousEol) {
                        $this->addWarning('Previous Text found', $token);
                    }

                    // This error shouldn't be reported
                    $nextText = $this->findNext(Token::TEXT_TYPE, $tokens, $tokenPosition + 1);
                    if (false !== $nextText) {
                        $this->addWarning('Next Text found', $token);
                    }

                    // This error should be reported
                    $nextEol = $this->findNext(Token::EOF_TYPE, $tokens, $tokenPosition + 1);
                    if (false !== $nextEol) {
                        $this->addError('Next EOL found', $token);
                    }
                }

                if (Token::EOF_TYPE === $token->getType()) {
                    // Ensure calling findNext on last token doesn't fail
                    $nextEof = $this->findNext(Token::EOF_TYPE, $tokens, $tokenPosition + 1);
                    if (false !== $nextEof) {
                        $this->addWarning('Next EOF found', $token);
                    }

                    // This error shouldn't be reported
                    $previousEof = $this->findPrevious(Token::EOF_TYPE, $tokens, $tokenPosition - 1);
                    if (false !== $previousEof) {
                        $this->addWarning('Previous Text found', $token);
                    }

                    // This error should be reported
                    $previousText = $this->findPrevious(Token::TEXT_TYPE, $tokens, $tokenPosition - 1);
                    if (false !== $previousText) {
                        $this->addError('Previous Text found', $token);
                    }
                }
            }
        };
        $rule->lintFile([
            new Token(Token::TEXT_TYPE, 0, 0, 'fakeFile.html.twig'),
            new Token(Token::EOL_TYPE, 1, 0, 'fakeFile.html.twig'),
            new Token(Token::EOL_TYPE, 2, 0, 'fakeFile.html.twig'),
            new Token(Token::EOL_TYPE, 3, 0, 'fakeFile.html.twig'),
            new Token(Token::EOL_TYPE, 4, 0, 'fakeFile.html.twig'),
            new Token(Token::EOF_TYPE, 5, 0, 'fakeFile.html.twig'),
        ], $report);

        static::assertSame(0, $report->getTotalWarnings());
        static::assertSame(2, $report->getTotalErrors());
    }

    /**
     * @param array<int> $expectedLines
     *
     * @dataProvider ignoredViolationsDataProvider
     */
    public function testIgnoredViolations(string $filePath, array $expectedLines): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);
        $ruleset = new Ruleset();

        $ruleset->addRule(new FakeRule());
        $report = $linter->run([new SplFileInfo($filePath)], $ruleset);
        $messages = $report->getFileViolations($filePath);

        static::assertSame(
            $expectedLines,
            array_map(
                static fn (Violation $violation) => $violation->getLine(),
                $messages,
            ),
        );
    }

    /**
     * @return iterable<array-key, array{string, array<int>}>
     */
    public static function ignoredViolationsDataProvider(): iterable
    {
        yield [
            __DIR__.'/Fixtures/disable0.twig',
            [1],
        ];
        yield [
            __DIR__.'/Fixtures/disable1.twig',
            [],
        ];
        yield [
            __DIR__.'/Fixtures/disable2.twig',
            [3, 6, 8, 9, 11],
        ];
    }
}
