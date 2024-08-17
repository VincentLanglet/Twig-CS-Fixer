<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\NamedArgumentSpacing;

use TwigCsFixer\Rules\Operator\NamedArgumentSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class NamedArgumentSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new NamedArgumentSpacingRule(), [
            'NamedArgumentSpacing.After:1:12' => 'Expecting 0 whitespace after "="; found 1.',
            'NamedArgumentSpacing.Before:1:12' => 'Expecting 0 whitespace before "="; found 1.',
            'NamedArgumentSpacing.After:1:24' => 'Expecting 1 whitespace after ":"; found 0.',
            'NamedArgumentSpacing.Before:1:24' => 'Expecting 0 whitespace before ":"; found 1.',
        ]);
    }
}
