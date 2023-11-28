<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\TrailingCommaSingleLine;

use TwigCsFixer\Rules\TrailingCommaSingleLineRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class TrailingCommaSingleLineTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TrailingCommaSingleLineRule(), [
            [2 => 9],
            [4 => 13],
            [6 => 12],
        ]);
    }
}
