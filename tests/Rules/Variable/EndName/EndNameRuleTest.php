<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Variable\EndName;

use TwigCsFixer\Rules\Variable\EndNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

class EndNameRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new EndNameRule(), [
            'EndName.Error:2:4' => 'The endmacro must have the "test" name.',
            'EndName.Error:6:4' => 'The endmacro must have the "outer_macro" name.',
            'EndName.Error:9:8' => 'The endmacro must have the "inner_macro" name.',
            'EndName.Error:14:4' => 'The endblock must have the "test" name.',
            'EndName.Error:18:4' => 'The endblock must have the "outer_block" name.',
            'EndName.Error:21:8' => 'The endblock must have the "inner_block" name.',
        ]);

        $blockPath = __DIR__.'/EndNameBlockRuleTest.twig';
        $macroPath = __DIR__.'/EndNameMacroRuleTest.twig';

        $this->checkRule(new EndNameRule(), [
            'EndName.Error:2:4' => 'The endblock must have the "test" name.',
            'EndName.Error:6:4' => 'The endblock must have the "outer_block" name.',
            'EndName.Error:9:8' => 'The endblock must have the "inner_block" name.',
        ], filePath: $blockPath);

        $this->checkRule(new EndNameRule(['block']), [
            'EndName.Error:2:4' => 'The endblock must have the "test" name.',
            'EndName.Error:6:4' => 'The endblock must have the "outer_block" name.',
            'EndName.Error:9:8' => 'The endblock must have the "inner_block" name.',
        ], filePath: $blockPath);

        $this->checkRule(new EndNameRule(), [
            'EndName.Error:2:4' => 'The endmacro must have the "test" name.',
            'EndName.Error:6:4' => 'The endmacro must have the "outer_macro" name.',
            'EndName.Error:9:8' => 'The endmacro must have the "inner_macro" name.',
        ], filePath: $macroPath);

        $this->checkRule(new EndNameRule(['macro']), [
            'EndName.Error:2:4' => 'The endmacro must have the "test" name.',
            'EndName.Error:6:4' => 'The endmacro must have the "outer_macro" name.',
            'EndName.Error:9:8' => 'The endmacro must have the "inner_macro" name.',
        ], filePath: $macroPath);

        $this->checkRule(new EndNameRule(['block']), [
        ], filePath: $macroPath);

        $this->checkRule(new EndNameRule(['macro']), [
        ], filePath: $blockPath);

        $this->checkRule(new EndNameRule(['fake']), [
        ], filePath: $blockPath);

        $this->checkRule(new EndNameRule(['fake']), [
        ], filePath: $macroPath);
    }
}
