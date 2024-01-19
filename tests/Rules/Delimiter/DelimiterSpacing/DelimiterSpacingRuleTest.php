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
            'DelimiterSpacing.After:15:1',
            'DelimiterSpacing.Before:15:12',
            'DelimiterSpacing.After:15:15',
            'DelimiterSpacing.Before:15:25',
        ]);
    }
}
