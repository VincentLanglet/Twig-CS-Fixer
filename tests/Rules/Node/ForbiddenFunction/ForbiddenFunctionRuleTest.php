<?php

namespace TwigCsFixer\Tests\Rules\Node\ForbiddenFunction;

use TwigCsFixer\Rules\Node\ForbiddenFunctionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

class ForbiddenFunctionRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'functions' => ['foo'],
            ],
            (new ForbiddenFunctionRule(['foo']))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new ForbiddenFunctionRule(['trans']), [
            'ForbiddenFunction.Error:8' => 'Function "trans" is not allowed.',
        ]);
    }
}
