<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Literal\CompactHashRule;

use TwigCsFixer\Rules\Literal\CompactHashRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class CompactHashRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['compact' => false],
            (new CompactHashRule())->getConfiguration()
        );
        static::assertSame(
            ['compact' => true],
            (new CompactHashRule(true))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new CompactHashRule(), [
            'CompactHash.Error:1:38' => 'Hash key "thing" should be explicit.',
        ]);
    }

    public function testRuleWithSingleQuote(): void
    {
        $this->checkRule(new CompactHashRule(true), [
            'CompactHash.Error:1:5' => 'Hash key "foo" should be implicit.',
        ], fixedFilePath: __DIR__.'/CompactHashRuleTest.compact.fixed.twig');
    }
}
