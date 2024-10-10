<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\OperatorNameSpacing;

use TwigCsFixer\Rules\Operator\OperatorNameSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class OperatorNameSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new OperatorNameSpacingRule(), [
            'OperatorNameSpacing.Error:2:13' => 'A single line operator should not have consecutive spaces.',
            'OperatorNameSpacing.Error:3:13' => 'A single line operator should not have consecutive spaces.',
            'OperatorNameSpacing.Error:4:10' => 'A single line operator should not have consecutive spaces.',
        ]);
    }
}
