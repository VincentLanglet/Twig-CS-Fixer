<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\TrailingSpace;

use TwigCsFixer\Rules\Whitespace\TrailingSpaceRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class TrailingSpaceTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TrailingSpaceRule(), [
            'TrailingSpace.Error:2:33',
            'TrailingSpace.Error:4:23',
        ]);
    }

    public function testRuleWithTab(): void
    {
        $this->checkRule(new TrailingSpaceRule(), [
            'TrailingSpace.Error:2:32',
            'TrailingSpace.Error:4:21',
        ], __DIR__.'/TrailingSpaceTest.tab.twig');
    }

    public function testRuleWithEmptyFile(): void
    {
        $this->checkRule(
            new TrailingSpaceRule(),
            [],
            __DIR__.'/TrailingSpaceTest.empty.twig'
        );

        $this->checkRule(new TrailingSpaceRule(), [
            'TrailingSpace.Error:1:2',
        ], __DIR__.'/TrailingSpaceTest.empty2.twig');
    }
}
