<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\Indent;

use TwigCsFixer\Rules\Whitespace\IndentRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class IndentRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(['space_ratio' => 4], (new IndentRule())->getConfiguration());
        static::assertSame(['space_ratio' => 2], (new IndentRule(2))->getConfiguration());
    }

    public function testRule(): void
    {
        $this->checkRule(new IndentRule(), [
            'Indent.Error:2:1',
            'Indent.Error:4:1',
        ]);
    }

    public function testRuleWithSpaceRatio(): void
    {
        $this->checkRule(
            new IndentRule(2),
            [
                'Indent.Error:2:1',
                'Indent.Error:4:1',
            ],
            __DIR__.'/IndentRuleTest.twig',
            __DIR__.'/IndentRuleTest.fixed2.twig',
        );
    }
}
