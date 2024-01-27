<?php

declare(strict_types=1);

namespace Rules\Function\IncludeFunction;

use TwigCsFixer\Rules\Function\IncludeFunctionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class IncludeFunctionRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new IncludeFunctionRule(), [
            'IncludeFunction.Error:1:4',
            'IncludeFunction.Error:2:4',
            'IncludeFunction.Error:3:4',
            'IncludeFunction.Error:4:4',
            'IncludeFunction.Error:5:4',
            'IncludeFunction.Error:6:4',
            'IncludeFunction.Error:7:4',
            'IncludeFunction.Error:8:4',
            'IncludeFunction.Error:9:4',
            'IncludeFunction.Error:10:4',
            'IncludeFunction.Error:11:4',
            'IncludeFunction.Error:12:4',
            'IncludeFunction.Error:13:4',
            'IncludeFunction.Error:14:4',
            'IncludeFunction.Error:15:5',
            'IncludeFunction.Error:16:6',
        ]);
    }
}
