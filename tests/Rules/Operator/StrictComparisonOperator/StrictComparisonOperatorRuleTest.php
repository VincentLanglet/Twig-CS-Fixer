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
            'StrictComparisonOperator.Error:1:6' => 'Use strict comparison operator "===" instead of "same as".',
            'StrictComparisonOperator.Error:2:6' => 'Use strict comparison operator "!==" instead of "not same as".',
            'StrictComparisonOperator.Error:3:6' => 'Use strict comparison operator "===" instead of "same as".',
            'StrictComparisonOperator.Error:4:6' => 'Use strict comparison operator "!==" instead of "not same as".',
            'StrictComparisonOperator.Error:5:6' => 'Use strict comparison operator "===" instead of "same as".',
            'StrictComparisonOperator.Error:6:6' => 'Use strict comparison operator "!==" instead of "not same as".',
            'StrictComparisonOperator.Error:7:6' => 'Use strict comparison operator "===" instead of "same as".',
            'StrictComparisonOperator.Error:10:6' => 'Use strict comparison operator "===" instead of "same as".',
        ]);
    }
}
