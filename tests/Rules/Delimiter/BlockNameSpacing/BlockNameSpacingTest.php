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
            'BlockNameSpacing.After.BlockName:1:5',
            'BlockNameSpacing.Before.BlockName:1:5',
            'BlockNameSpacing.After.BlockName:3:3',
            'BlockNameSpacing.Before.BlockName:3:3',
        ]);
    }
}
