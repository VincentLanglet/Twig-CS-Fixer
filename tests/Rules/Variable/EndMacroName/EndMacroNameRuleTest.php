<?php

namespace TwigCsFixer\Tests\Rules\Variable\EndMacroName;

use TwigCsFixer\Rules\Variable\EndBlockNameRule;
use TwigCsFixer\Rules\Variable\EndMacroNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

class EndMacroNameRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new EndMacroNameRule(), [
            'EndMacroName.Error:2:4' => 'The end macro must have the "test" name.',
            'EndMacroName.Error:6:4' => 'The end macro must have the "outer_macro" name.',
            'EndMacroName.Error:9:8' => 'The end macro must have the "inner_macro" name.',
        ]);
    }
}
