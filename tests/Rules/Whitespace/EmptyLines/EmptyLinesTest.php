<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\EmptyLines;

use TwigCsFixer\Rules\Whitespace\EmptyLinesRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class EmptyLinesTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new EmptyLinesRule(), [
            'EmptyLines.Error.Eol:2:1',
            'EmptyLines.Error.Eol:5:1',
            'EmptyLines.Error.Eol:10:1',
        ]);
    }
}
