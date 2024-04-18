<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\String\SimpleQuote;

use TwigCsFixer\Rules\String\SingleQuoteRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

class SingleQuoteRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['skip_string_containing_single_quote' => true],
            (new SingleQuoteRule())->getConfiguration()
        );
        static::assertSame(
            ['skip_string_containing_single_quote' => false],
            (new SingleQuoteRule(false))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new SingleQuoteRule(), [
            'SingleQuote.Error:5:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:10:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:11:15' => 'String should be defined with single quotes.',
        ]);
    }

    public function testRuleWithoutSkippingSingleQuote(): void
    {
        $this->checkRule(new SingleQuoteRule(false), [
            'SingleQuote.Error:5:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:6:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:7:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:10:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:11:15' => 'String should be defined with single quotes.',
        ], fixedFilePath: __DIR__.'/SingleQuoteRuleTest.all.fixed.twig');
    }
}
