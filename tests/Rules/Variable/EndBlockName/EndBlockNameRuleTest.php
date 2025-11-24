<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Variable\EndBlockName;

use TwigCsFixer\Rules\Variable\EndBlockNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

class EndBlockNameRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new EndBlockNameRule(), [
            'EndBlockName.Error:2:4' => 'The endblock must have the "test" name.',
            'EndBlockName.Error:6:4' => 'The endblock must have the "outer_block" name.',
            'EndBlockName.Error:9:8' => 'The endblock must have the "inner_block" name.',
        ]);
    }
}
