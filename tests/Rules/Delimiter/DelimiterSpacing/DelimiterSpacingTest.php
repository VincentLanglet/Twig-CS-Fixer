<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\DelimiterSpacing;

use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class DelimiterSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new DelimiterSpacingRule(), [
            'DelimiterSpacing.After.BlockStart:15:1',
            'DelimiterSpacing.Before.BlockEnd:15:12',
            'DelimiterSpacing.After.BlockStart:15:15',
            'DelimiterSpacing.Before.BlockEnd:15:25',
        ]);
    }
}
