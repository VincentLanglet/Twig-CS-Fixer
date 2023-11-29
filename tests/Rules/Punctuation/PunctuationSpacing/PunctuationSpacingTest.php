<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\PunctuationSpacing;

use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class PunctuationSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new PunctuationSpacingRule(), [
            [3 => 4],
            [3 => 10],
            [4 => 4],
            [4 => 10],
            [4 => 16],
            [4 => 22],
            [4 => 28],
            [5 => 12],
            [5 => 16],
            [5 => 20],
            [5 => 24],
            [6 => 6],
            [6 => 6],
            [7 => 12],
            [7 => 15],
        ]);
    }
}
