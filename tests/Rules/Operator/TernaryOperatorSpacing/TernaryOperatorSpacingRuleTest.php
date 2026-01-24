<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\TernaryOperatorSpacing;

use TwigCsFixer\Rules\Operator\TernaryOperatorSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class TernaryOperatorSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TernaryOperatorSpacingRule(), [
            'TernaryOperatorSpacing.After:1:10' => 'Expecting 1 whitespace after "?"; found 2.',
            'TernaryOperatorSpacing.Before:1:10' => 'Expecting 1 whitespace before "?"; found 2.',
            'TernaryOperatorSpacing.After:1:19' => 'Expecting 1 whitespace after ":"; found 2.',
            'TernaryOperatorSpacing.Before:1:19' => 'Expecting 1 whitespace before ":"; found 2.',
            'TernaryOperatorSpacing.After:2:10' => 'Expecting 1 whitespace after "?"; found 2.',
            'TernaryOperatorSpacing.Before:2:10' => 'Expecting 1 whitespace before "?"; found 2.',
        ]);
    }
}
