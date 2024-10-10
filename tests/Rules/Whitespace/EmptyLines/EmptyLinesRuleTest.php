<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\EmptyLines;

use TwigCsFixer\Rules\Whitespace\EmptyLinesRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class EmptyLinesRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new EmptyLinesRule(), [
            'EmptyLines.Error:2:1' => 'More than 1 empty line is not allowed, found 2',
            'EmptyLines.Error:5:1' => 'More than 1 empty line is not allowed, found 2',
            'EmptyLines.Error:10:1' => 'More than 1 empty line is not allowed, found 3',
        ]);
    }
}
