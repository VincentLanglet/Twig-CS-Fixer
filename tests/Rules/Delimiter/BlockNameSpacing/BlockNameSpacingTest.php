<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\BlockNameSpacing;

use TwigCsFixer\Rules\Delimiter\BlockNameSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class BlockNameSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new BlockNameSpacingRule(), [
            [1 => 5],
            [1 => 5],
            [3 => 3],
            [3 => 3],
        ]);
    }
}
