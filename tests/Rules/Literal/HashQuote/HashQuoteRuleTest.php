<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Literal\HashQuote;

use TwigCsFixer\Rules\Literal\HashQuoteRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class HashQuoteRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['useQuote' => false],
            (new HashQuoteRule())->getConfiguration()
        );
        static::assertSame(
            ['useQuote' => true],
            (new HashQuoteRule(true))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new HashQuoteRule(), [
            'HashQuote.Error:7:16' => 'The hash key "a" does not require to be quoted.',
            'HashQuote.Error:11:18' => 'The hash key "123" does not require to be quoted.',
        ]);
    }

    public function testRuleWithSingleQuote(): void
    {
        $this->checkRule(new HashQuoteRule(true), [
            'HashQuote.Error:8:16' => 'The hash key "a" should be quoted.',
            'HashQuote.Error:10:18' => 'The hash key "123" should be quoted.',
            'HashQuote.Error:12:26' => 'The hash key "0123" should be quoted.',
            'HashQuote.Error:14:17' => 'The hash key "12.3" should be quoted.',
        ], fixedFilePath: __DIR__.'/HashQuoteRuleTest.with.fixed.twig');
    }
}
