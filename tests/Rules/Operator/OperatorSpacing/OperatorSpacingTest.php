<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\OperatorSpacing;

use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class OperatorSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new OperatorSpacingRule(), [
            [1 => 5],
            [1  => 5],
            [2  => 5],
            [2  => 5],
            [3  => 5],
            [3  => 5],
            [4  => 5],
            [4  => 5],
            [5  => 5],
            [5  => 5],
            [6  => 5],
            [6  => 5],
            [7  => 5],
            [7  => 5],
            [8  => 7],
            [8  => 7],
            [9  => 10],
            [9  => 10],
            [9  => 19],
            [9  => 19],
            [10 => 5],
            [10 => 5],
            [11 => 4],
            [12 => 11],
            [12 => 11],
            [13 => 11],
            [13 => 11],
            [14 => 7],
            [14 => 7],
            [15 => 7],
            [15 => 7],
            [19 => 5],
            [19 => 5],
            [20 => 5],
            [20 => 5],
            [22 => 6],
            [33 => 10],
            [33 => 10],
            [35 => 13],
            [35 => 13],
            [36 => 13],
            [36 => 13],
            [37 => 13],
            [37 => 13],
        ]);
    }

    public function testRuleWithTab(): void
    {
        $this->checkRule(
            new OperatorSpacingRule(),
            [],
            __DIR__.'/OperatorSpacingTest.tab.twig'
        );
    }
}
