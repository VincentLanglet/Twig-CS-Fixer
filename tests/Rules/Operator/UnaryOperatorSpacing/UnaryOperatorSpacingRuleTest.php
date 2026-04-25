<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\TernaryOperatorSpacing;

use TwigCsFixer\Rules\Operator\UnaryOperatorSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class UnaryOperatorSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new UnaryOperatorSpacingRule(), [
            'UnaryOperatorSpacing.After:1:4' => 'Expecting 1 whitespace after "not"; found 3.',
            'UnaryOperatorSpacing.After:4:15' => 'Expecting 0 whitespace after "-"; found 1.',
        ]);
    }
}
