<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\PunctuationSpacing;

use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class PunctuationSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new PunctuationSpacingRule(), [
            'PunctuationSpacing.After:3:4',
            'PunctuationSpacing.Before:3:10',
            'PunctuationSpacing.After:4:4',
            'PunctuationSpacing.Before:4:10',
            'PunctuationSpacing.Before:4:16',
            'PunctuationSpacing.Before:4:22',
            'PunctuationSpacing.Before:4:28',
            'PunctuationSpacing.After:5:12',
            'PunctuationSpacing.Before:5:16',
            'PunctuationSpacing.Before:5:20',
            'PunctuationSpacing.Before:5:24',
            'PunctuationSpacing.After:6:6',
            'PunctuationSpacing.Before:6:6',
            'PunctuationSpacing.Before:7:12',
            'PunctuationSpacing.Before:7:15',
        ]);
    }
}
