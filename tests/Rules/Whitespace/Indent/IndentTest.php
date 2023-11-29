<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\Indent;

use TwigCsFixer\Rules\Whitespace\IndentRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class IndentTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(['space_ratio' => 4], (new IndentRule())->getConfiguration());
        static::assertSame(['space_ratio' => 2], (new IndentRule(2))->getConfiguration());
    }

    public function testRule(): void
    {
        $this->checkRule(new IndentRule(), [
            [2  => 1],
            [4  => 1],
        ]);
    }

    public function testRuleWithSpaceRatio(): void
    {
        $this->checkRule(
            new IndentRule(2),
            [
                [2  => 1],
                [4  => 1],
            ],
            __DIR__.'/IndentTest.twig',
            __DIR__.'/IndentTest.fixed2.twig',
        );
    }
}
