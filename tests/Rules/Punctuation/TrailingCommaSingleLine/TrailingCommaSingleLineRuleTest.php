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
            'TrailingCommaSingleLine.Error:2:9',
            'TrailingCommaSingleLine.Error:4:13',
            'TrailingCommaSingleLine.Error:6:12',
        ]);
    }
}
