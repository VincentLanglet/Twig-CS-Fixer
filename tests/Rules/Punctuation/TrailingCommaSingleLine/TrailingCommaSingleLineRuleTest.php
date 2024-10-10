<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\TrailingCommaSingleLine;

use TwigCsFixer\Rules\Punctuation\TrailingCommaSingleLineRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class TrailingCommaSingleLineRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TrailingCommaSingleLineRule(), [
            'TrailingCommaSingleLine.Error:2:8' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:4:12' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:6:11' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:34:6' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:37:17' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:40:15' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
        ]);
    }
}
