<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Punctuation\TrailingCommaSingleLine;

use TwigCsFixer\Rules\Punctuation\TrailingCommaSingleLineRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class TrailingCommaSingleLineTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new TrailingCommaSingleLineRule(), [
            'TrailingCommaSingleLine.Error.Punctuation:2:9',
            'TrailingCommaSingleLine.Error.Punctuation:4:13',
            'TrailingCommaSingleLine.Error.Punctuation:6:12',
        ]);
    }
}
