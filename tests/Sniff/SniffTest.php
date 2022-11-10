<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Sniff\AbstractSniff;
use TwigCsFixer\Token\Token;

final class SniffTest extends TestCase
{
    private AbstractSniff $sniff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sniff = new class () extends AbstractSniff {
            protected function process(int $tokenPosition, array $tokens): void
            {
                $token = $tokens[$tokenPosition];

                if (0 === $tokenPosition) {
                    $this->addWarning('Fake Warning', $token);
                    $this->addError('Fake Error', $token);
                    $this->addFixableWarning('Fake fixable warning', $token);
                    $this->addFixableError('Fake fixable error', $token);
                }

                if (Token::EOF_TYPE !== $token->getType()) {
                    return;
                }

                $nextEof = $this->findNext(Token::EOF_TYPE, $tokens, $tokenPosition + 1);
                if (false !== $nextEof) {
                    $this->addError('Next EOF found', $token);
                }

                $previousEof = $this->findPrevious(Token::EOF_TYPE, $tokens, $tokenPosition - 1);
                if (false !== $previousEof) {
                    $this->addError('Previous EOF found', $token);
                }
            }
        };
    }

    public function testSniffWithoutReport(): void
    {
        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage(sprintf(
            'Cannot add a message to the sniff "%s" without a report.',
            AbstractSniff::class
        ));

        $this->sniff->processFile([new Token(Token::EOF_TYPE, 0, 0, 'fakeFile.html.twig')]);
    }

    public function testSniffWithReport(): void
    {
        $report = new Report([new SplFileInfo('fakeFile.html.twig')]);

        $this->sniff->enableReport($report);
        $this->sniff->processFile([new Token(Token::EOF_TYPE, 0, 0, 'fakeFile.html.twig')]);

        static::assertSame(2, $report->getTotalWarnings());
        static::assertSame(2, $report->getTotalErrors());
    }

    public function testSniffWithReport2(): void
    {
        $report = new Report([new SplFileInfo('fakeFile.html.twig')]);

        $this->sniff->enableReport($report);
        $this->sniff->processFile([
            new Token(Token::EOF_TYPE, 0, 0, 'fakeFile.html.twig'),
            new Token(Token::EOF_TYPE, 1, 0, 'fakeFile.html.twig'),
        ]);

        static::assertSame(2, $report->getTotalWarnings());
        static::assertSame(4, $report->getTotalErrors());
    }
}
