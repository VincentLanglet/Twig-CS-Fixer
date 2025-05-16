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
            'CompactHash.Error:1:60' => 'Hash key "thing" should be explicit.',
            'CompactHash.Error:3:20' => 'Hash key "product" should be explicit.',
            'CompactHash.Error:5:5' => 'Hash key "product" should be explicit.',
        ]);
    }

    public function testRuleWithCompact(): void
    {
        $this->checkRule(new CompactHashRule(true), [
            'CompactHash.Error:1:5' => 'Hash key "foo" should be implicit.',
            'CompactHash.Error:8:20' => 'Hash key "product" should be implicit.',
            'CompactHash.Error:10:5' => 'Hash key "product" should be implicit.',
            'CompactHash.Error:13:20' => 'Hash key "product" should be implicit.',
            'CompactHash.Error:15:5' => 'Hash key "product" should be implicit.',
            'CompactHash.Error:18:20' => 'Hash key "product" should be implicit.',
            'CompactHash.Error:20:5' => 'Hash key "product" should be implicit.',
        ], fixedFilePath: __DIR__.'/CompactHashRuleTest.compact.fixed.twig');
    }
}
