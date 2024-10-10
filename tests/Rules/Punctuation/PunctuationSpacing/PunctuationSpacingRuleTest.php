<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\PunctuationSpacing;

use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class PunctuationSpacingRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'before' => [
                    ')' => 0,
                    ']' => 0,
                    '}' => 0,
                    ':' => 0,
                    '.' => 0,
                    ',' => 0,
                    '|' => 0,
                ],
                'after' => [
                    '(' => 0,
                    '[' => 0,
                    '{' => 0,
                    '.' => 0,
                    '|' => 0,
                    ':' => 1,
                    ',' => 1,
                ],
            ],
            (new PunctuationSpacingRule())->getConfiguration()
        );

        static::assertSame(
            [
                'before' => [
                    '}' => null,
                    ')' => 0,
                    ']' => 0,
                    ':' => 0,
                    '.' => 0,
                    ',' => 0,
                    '|' => 0,
                ],
                'after' => [
                    '{' => null,
                    '(' => 0,
                    '[' => 0,
                    '.' => 0,
                    '|' => 0,
                    ':' => 1,
                    ',' => 1,
                ],
            ],
            (new PunctuationSpacingRule(['}' => null], ['{' => null]))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new PunctuationSpacingRule(), [
            'PunctuationSpacing.After:3:4' => 'Expecting 0 whitespace after "("; found 1.',
            'PunctuationSpacing.Before:3:10' => 'Expecting 0 whitespace before ")"; found 1.',
            'PunctuationSpacing.After:4:4' => 'Expecting 0 whitespace after "{"; found 1.',
            'PunctuationSpacing.Before:4:10' => 'Expecting 0 whitespace before ":"; found 1.',
            'PunctuationSpacing.Before:4:16' => 'Expecting 0 whitespace before ","; found 1.',
            'PunctuationSpacing.Before:4:22' => 'Expecting 0 whitespace before ":"; found 1.',
            'PunctuationSpacing.Before:4:28' => 'Expecting 0 whitespace before "}"; found 1.',
            'PunctuationSpacing.After:5:12' => 'Expecting 0 whitespace after "["; found 1.',
            'PunctuationSpacing.Before:5:16' => 'Expecting 0 whitespace before ","; found 1.',
            'PunctuationSpacing.Before:5:20' => 'Expecting 0 whitespace before ","; found 1.',
            'PunctuationSpacing.Before:5:24' => 'Expecting 0 whitespace before "]"; found 1.',
            'PunctuationSpacing.After:6:6' => 'Expecting 0 whitespace after "|"; found 1.',
            'PunctuationSpacing.Before:6:6' => 'Expecting 0 whitespace before "|"; found 1.',
            'PunctuationSpacing.Before:7:12' => 'Expecting 0 whitespace before "}"; found 1.',
            'PunctuationSpacing.Before:7:15' => 'Expecting 0 whitespace before "]"; found 1.',
        ]);
    }

    public function testRuleWithConfiguration(): void
    {
        $this->checkRule(new PunctuationSpacingRule(['}' => null], ['{' => null]), [
            'PunctuationSpacing.After:3:4' => 'Expecting 0 whitespace after "("; found 1.',
            'PunctuationSpacing.Before:3:10' => 'Expecting 0 whitespace before ")"; found 1.',
            'PunctuationSpacing.Before:4:10' => 'Expecting 0 whitespace before ":"; found 1.',
            'PunctuationSpacing.Before:4:16' => 'Expecting 0 whitespace before ","; found 1.',
            'PunctuationSpacing.Before:4:22' => 'Expecting 0 whitespace before ":"; found 1.',
            'PunctuationSpacing.After:5:12' => 'Expecting 0 whitespace after "["; found 1.',
            'PunctuationSpacing.Before:5:16' => 'Expecting 0 whitespace before ","; found 1.',
            'PunctuationSpacing.Before:5:20' => 'Expecting 0 whitespace before ","; found 1.',
            'PunctuationSpacing.Before:5:24' => 'Expecting 0 whitespace before "]"; found 1.',
            'PunctuationSpacing.After:6:6' => 'Expecting 0 whitespace after "|"; found 1.',
            'PunctuationSpacing.Before:6:6' => 'Expecting 0 whitespace before "|"; found 1.',
            'PunctuationSpacing.Before:7:15' => 'Expecting 0 whitespace before "]"; found 1.',
        ], fixedFilePath: false);
    }
}
