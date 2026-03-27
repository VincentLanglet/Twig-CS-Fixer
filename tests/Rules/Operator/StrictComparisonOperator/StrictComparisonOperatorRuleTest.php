<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\StrictComparisonOperator;

use TwigCsFixer\Rules\Operator\StrictComparisonOperatorRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class StrictComparisonOperatorRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new StrictComparisonOperatorRule(), [
            'StrictComparisonOperator.Error:1:6' => 'Use strict comparison operators === / !== instead of same as / not same as.',
            'StrictComparisonOperator.Error:2:6' => 'Use strict comparison operators === / !== instead of same as / not same as.',
            'StrictComparisonOperator.Error:3:6' => 'Use strict comparison operators === / !== instead of same as / not same as.',
            'StrictComparisonOperator.Error:4:6' => 'Use strict comparison operators === / !== instead of same as / not same as.',
        ]);
    }
}
