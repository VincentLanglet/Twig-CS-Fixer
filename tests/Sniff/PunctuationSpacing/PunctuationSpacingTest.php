<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\PunctuationSpacing;

use TwigCsFixer\Sniff\PunctuationSpacingSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class PunctuationSpacingTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new PunctuationSpacingSniff(), [
            [3 => 4],
            [3 => 10],
            [4 => 4],
            [4 => 10],
            [4 => 16],
            [4 => 22],
            [4 => 28],
            [5 => 12],
            [5 => 16],
            [5 => 20],
            [5 => 24],
            [6 => 6],
            [6 => 6],
            [7 => 12],
            [7 => 15],
        ]);
    }

    public function testSniffWithConfig(): void
    {
        $this->checkSniff(
            new PunctuationSpacingSniff(
                [
                    ')' => 1,
                    '}' => ['default' => 1, 'byPreviousValue' => ['{' => 0]],
                    ']' => null,
                    ':' => null,
                    ',' => null,
                    '|' => 1,
                ],
                [
                    '(' => 1,
                    '{' => ['default' => 1, 'byNextValue' => ['}' => 0]],
                    '[' => null,
                    ':' => null,
                    ',' => 1,
                    '|' => 1,
                ]
            ),
            [],
            __DIR__.'/PunctuationSpacingTest.config.twig'
        );
    }
}
