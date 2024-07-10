<?php

namespace TwigCsFixer\Tests\Rules\Node\ForbiddenBlock;

use TwigCsFixer\Rules\Node\ForbiddenBlockRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

class ForbiddenBlockRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'blocks' => ['foo'],
            ],
            (new ForbiddenBlockRule(['foo']))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new ForbiddenBlockRule(['trans']), [
            'ForbiddenBlock.Error:7' => 'Block "trans" is not allowed.',
        ]);
    }
}
