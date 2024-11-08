<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Function\RenderFunction;

use TwigCsFixer\Rules\Function\IncludeToRenderFunctionRule;
use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class IncludeToRenderFunctionRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(
            [
                new IncludeToRenderFunctionRule(),
                // Extra rule for a better diff
                new PunctuationSpacingRule(),
            ],
            [
                'IncludeToRender.Error:13:4' => 'Include function must be used instead of include tag.',
                'IncludeToRender.Error:14:4' => 'Include function must be used instead of include tag.',
                'IncludeToRender.Error:15:5' => 'Include function must be used instead of include tag.',
                'IncludeToRender.Error:16:5' => 'Include function must be used instead of include tag.',
            ]
        );
    }
}
