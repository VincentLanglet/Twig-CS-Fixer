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
            'TrailingCommaMultiLine.Error:11:1' => 'Multi-line arrays, objects and parameters lists should have trailing comma.',
            'TrailingCommaMultiLine.Error:19:1' => 'Multi-line arrays, objects and parameters lists should have trailing comma.',
            'TrailingCommaMultiLine.Error:27:1' => 'Multi-line arrays, objects and parameters lists should have trailing comma.',
        ]);
    }

    public function testRuleWithoutTrailingComma(): void
    {
        $this->checkRule(new TrailingCommaMultiLineRule(false), [
            'TrailingCommaMultiLine.Error:15:1' => 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaMultiLine.Error:23:1' => 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaMultiLine.Error:31:1' => 'Multi-line arrays, objects and parameters lists should not have trailing comma.',
        ], fixedFilePath: __DIR__.'/TrailingCommaMultiLineRuleTest.fixed2.twig');
    }
}
