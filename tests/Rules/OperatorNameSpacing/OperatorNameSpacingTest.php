<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\OperatorNameSpacing;

use TwigCsFixer\Rules\OperatorNameSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class OperatorNameSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new OperatorNameSpacingRule(), [
            [2 => 13],
            [3 => 13],
            [4 => 10],
        ]);
    }
}
