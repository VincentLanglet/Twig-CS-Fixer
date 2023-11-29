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
            [15 => 1],
            [15 => 12],
            [15 => 15],
            [15 => 25],
        ]);
    }
}
