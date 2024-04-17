<?php

namespace TwigCsFixer\Tests\Rules\String\SimpleQuote;

use TwigCsFixer\Rules\String\SingleQuoteRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

class SingleQuoteRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new SingleQuoteRule(), [
            'SingleQuote.Error:5:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:6:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:7:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:10:15' => 'String should be defined with single quotes.',
            'SingleQuote.Error:11:15' => 'String should be defined with single quotes.',
        ]);
    }
}
