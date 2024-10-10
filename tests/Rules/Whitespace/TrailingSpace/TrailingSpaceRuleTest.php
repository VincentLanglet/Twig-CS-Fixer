<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\TrailingSpace;

use TwigCsFixer\Rules\Whitespace\TrailingSpaceRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class TrailingSpaceRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TrailingSpaceRule(), [
            'TrailingSpace.Error:2:33' => 'A line should not end with blank space(s).',
            'TrailingSpace.Error:4:23' => 'A line should not end with blank space(s).',
        ]);
    }

    public function testRuleWithTab(): void
    {
        $this->checkRule(new TrailingSpaceRule(), [
            'TrailingSpace.Error:2:32' => 'A line should not end with blank space(s).',
            'TrailingSpace.Error:4:21' => 'A line should not end with blank space(s).',
        ], __DIR__.'/TrailingSpaceRuleTest.tab.twig');
    }

    public function testRuleWithEmptyFile(): void
    {
        $this->checkRule(
            new TrailingSpaceRule(),
            [],
            __DIR__.'/TrailingSpaceRuleTest.empty.twig'
        );

        $this->checkRule(new TrailingSpaceRule(), [
            'TrailingSpace.Error:1:2' => 'A line should not end with blank space(s).',
        ], __DIR__.'/TrailingSpaceRuleTest.empty2.twig');
    }
}
