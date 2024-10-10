<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\DelimiterSpacing;

use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class DelimiterSpacingRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            ['skipIfNewLine' => true],
            (new DelimiterSpacingRule())->getConfiguration()
        );
        static::assertSame(
            ['skipIfNewLine' => false],
            (new DelimiterSpacingRule(false))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new DelimiterSpacingRule(), [
            'DelimiterSpacing.After:15:1' => 'Expecting 1 whitespace after "{%-"; found 0.',
            'DelimiterSpacing.Before:15:12' => 'Expecting 1 whitespace before "-%}"; found 2.',
            'DelimiterSpacing.After:15:15' => 'Expecting 1 whitespace after "{%-"; found 2.',
            'DelimiterSpacing.Before:15:25' => 'Expecting 1 whitespace before "-%}"; found 0.',
        ]);
    }

    public function testRuleWithoutSkipIfNewLine(): void
    {
        $this->checkRule(new DelimiterSpacingRule(false), [
            'DelimiterSpacing.After:9:1' => 'Expecting 1 whitespace after "{{"; found newline.',
            'DelimiterSpacing.Before:11:1' => 'Expecting 1 whitespace before "}}"; found newline.',
            'DelimiterSpacing.After:12:1' => 'Expecting 1 whitespace after "{#"; found newline.',
            'DelimiterSpacing.Before:14:1' => 'Expecting 1 whitespace before "#}"; found newline.',
            'DelimiterSpacing.After:15:1' => 'Expecting 1 whitespace after "{%-"; found 0.',
            'DelimiterSpacing.Before:15:12' => 'Expecting 1 whitespace before "-%}"; found 2.',
            'DelimiterSpacing.After:15:15' => 'Expecting 1 whitespace after "{%-"; found 2.',
            'DelimiterSpacing.Before:15:25' => 'Expecting 1 whitespace before "-%}"; found 0.',
            'DelimiterSpacing.After:20:5' => 'Expecting 1 whitespace after "{{"; found newline.',
            'DelimiterSpacing.Before:22:5' => 'Expecting 1 whitespace before "}}"; found newline.',
            'DelimiterSpacing.After:33:1' => 'Expecting 1 whitespace after "{%"; found newline.',
            'DelimiterSpacing.Before:39:2' => 'Expecting 1 whitespace before "%}"; found newline.',
        ], fixedFilePath: __DIR__.'/DelimiterSpacingRuleTest.fixed2.twig');
    }
}
