<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\DelimiterSpacing;

use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class DelimiterSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new DelimiterSpacingRule(), [
            'DelimiterSpacing.After:15:1' => 'Expecting 1 whitespace after "{%-"; found 0',
            'DelimiterSpacing.Before:15:12' => 'Expecting 1 whitespace before "-%}"; found 2',
            'DelimiterSpacing.After:15:15' => 'Expecting 1 whitespace after "{%-"; found 2',
            'DelimiterSpacing.Before:15:25' => 'Expecting 1 whitespace before "-%}"; found 0',
        ]);
    }
}
