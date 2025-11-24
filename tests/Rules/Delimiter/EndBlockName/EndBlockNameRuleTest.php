<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\EndBlockName;

use TwigCsFixer\Rules\Delimiter\EndBlockNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

class EndBlockNameRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['blocks' => ['block', 'macro']],
            (new EndBlockNameRule())->getConfiguration()
        );
        static::assertSame(
            ['blocks' => ['block']],
            (new EndBlockNameRule(['block']))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new EndBlockNameRule(), [
            'EndBlockName.Error:2:4' => 'The endmacro must have the "test" name.',
            'EndBlockName.Error:6:4' => 'The endmacro must have the "outer_macro" name.',
            'EndBlockName.Error:9:8' => 'The endmacro must have the "inner_macro" name.',
            'EndBlockName.Error:14:4' => 'The endblock must have the "test" name.',
            'EndBlockName.Error:18:4' => 'The endblock must have the "outer_block" name.',
            'EndBlockName.Error:21:8' => 'The endblock must have the "inner_block" name.',
        ]);
    }

    public function testRuleWithBlock(): void
    {
        $this->checkRule(new EndBlockNameRule(['block']), [
            'EndBlockName.Error:14:4' => 'The endblock must have the "test" name.',
            'EndBlockName.Error:18:4' => 'The endblock must have the "outer_block" name.',
            'EndBlockName.Error:21:8' => 'The endblock must have the "inner_block" name.',
        ], fixedFilePath: __DIR__.'/EndNameBlockRuleTest.fixed.block.twig');
    }

    public function testRuleWithMacro(): void
    {
        $this->checkRule(new EndBlockNameRule(['macro']), [
            'EndBlockName.Error:2:4' => 'The endmacro must have the "test" name.',
            'EndBlockName.Error:6:4' => 'The endmacro must have the "outer_macro" name.',
            'EndBlockName.Error:9:8' => 'The endmacro must have the "inner_macro" name.',
        ], fixedFilePath: __DIR__.'/EndBlockNameRuleTest.fixed.macro.twig');
    }

    public function testRuleWithoutBlockName(): void
    {
        $this->checkRule(new EndBlockNameRule(['sandbox']), [], filePath: __DIR__.'/EndBlockNameRuleTest.custom.twig');
    }
}
