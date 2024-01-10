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
            'PunctuationSpacing.After.Punctuation:3:4',
            'PunctuationSpacing.Before.Punctuation:3:10',
            'PunctuationSpacing.After.Punctuation:4:4',
            'PunctuationSpacing.Before.Punctuation:4:10',
            'PunctuationSpacing.Before.Punctuation:4:16',
            'PunctuationSpacing.Before.Punctuation:4:22',
            'PunctuationSpacing.Before.Punctuation:4:28',
            'PunctuationSpacing.After.Punctuation:5:12',
            'PunctuationSpacing.Before.Punctuation:5:16',
            'PunctuationSpacing.Before.Punctuation:5:20',
            'PunctuationSpacing.Before.Punctuation:5:24',
            'PunctuationSpacing.After.Punctuation:6:6',
            'PunctuationSpacing.Before.Punctuation:6:6',
            'PunctuationSpacing.Before.Punctuation:7:12',
            'PunctuationSpacing.Before.Punctuation:7:15',
        ]);
    }
}
