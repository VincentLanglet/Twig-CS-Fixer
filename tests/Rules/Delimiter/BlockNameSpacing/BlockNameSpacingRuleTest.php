<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\BlockNameSpacing;

use TwigCsFixer\Rules\Delimiter\BlockNameSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class BlockNameSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new BlockNameSpacingRule(), [
            'BlockNameSpacing.After:1:5' => 'Expecting 1 whitespace after "extends"; found 0.',
            'BlockNameSpacing.Before:1:5' => 'Expecting 1 whitespace before "extends"; found 2.',
            'BlockNameSpacing.After:3:3' => 'Expecting 1 whitespace after "if"; found 4.',
            'BlockNameSpacing.Before:3:3' => 'Expecting 1 whitespace before "if"; found 0.',
        ]);
    }
}
