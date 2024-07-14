<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\TrailingCommaMultiLine;

use TwigCsFixer\Rules\Punctuation\TrailingCommaMultiLineRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class TrailingCommaMultiLineRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['comma' => true],
            (new TrailingCommaMultiLineRule())->getConfiguration()
        );
        static::assertSame(
            ['comma' => false],
            (new TrailingCommaMultiLineRule(false))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new TrailingCommaMultiLineRule(), [
            'TrailingCommaMultiLine.Error:10:6' => 'Multi-line arrays, objects and parameters lists should have trailing comma.',
            'TrailingCommaMultiLine.Error:18:8' => 'Multi-line arrays, objects and parameters lists should have trailing comma.',
            'TrailingCommaMultiLine.Error:26:6' => 'Multi-line arrays, objects and parameters lists should have trailing comma.',
        ]);
    }

    public function testRuleWithoutTrailingComma(): void
    {
        $this->checkRule(new TrailingCommaMultiLineRule(false), [
            'TrailingCommaMultiLine.Error:14:6' => 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaMultiLine.Error:22:8' => 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaMultiLine.Error:30:6' => 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
        ], fixedFilePath: __DIR__.'/TrailingCommaMultiLineRuleTest.fixed2.twig');
    }
}
