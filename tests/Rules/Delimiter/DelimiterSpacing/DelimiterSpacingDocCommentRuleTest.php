<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Delimiter\DelimiterSpacing;

use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class DelimiterSpacingDocCommentRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new DelimiterSpacingRule(), [
            'DelimiterSpacing.After:6:3' => 'Expecting 1 whitespace after "#"; found 2.',
            'DelimiterSpacing.After:7:1' => 'Expecting 1 whitespace after "{#"; found 0.',
            'DelimiterSpacing.Before:8:8' => 'Expecting 1 whitespace before "#}"; found 0.',
        ]);
    }
}
