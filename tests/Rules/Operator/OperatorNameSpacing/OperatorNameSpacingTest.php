<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\OperatorNameSpacing;

use TwigCsFixer\Rules\Operator\OperatorNameSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class OperatorNameSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new OperatorNameSpacingRule(), [
            'OperatorNameSpacing.Error:2:13',
            'OperatorNameSpacing.Error:3:13',
            'OperatorNameSpacing.Error:4:10',
        ]);
    }
}
