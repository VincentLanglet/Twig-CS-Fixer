<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Function\IncludeFunction;

use TwigCsFixer\Rules\Function\IncludeFunctionRule;
use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class IncludeFunctionRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(
            [
                new IncludeFunctionRule(),
                // Extra rule for a better diff
                new PunctuationSpacingRule(),
            ],
            [
                'IncludeFunction.Error:1:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:2:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:3:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:4:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:5:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:6:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:7:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:8:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:9:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:10:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:11:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:12:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:13:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:14:4' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:15:5' => 'Include function must be used instead of include tag.',
                'IncludeFunction.Error:16:5' => 'Include function must be used instead of include tag.',
            ]
        );
    }
}
