<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\TrailingCommaSingleLine;

use TwigCsFixer\Rules\Punctuation\TrailingCommaSingleLineRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class TrailingCommaSingleLineRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TrailingCommaSingleLineRule(), [
            'TrailingCommaSingleLine.Error:2:9' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:4:13' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:6:12' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:34:7' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:37:17' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
            'TrailingCommaSingleLine.Error:40:15' => 'Single-line arrays, objects and parameters lists should not have trailing comma.',
        ]);
    }
}
