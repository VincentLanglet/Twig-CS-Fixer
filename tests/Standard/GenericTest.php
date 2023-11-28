<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Standard;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\BlankEOFRule;
use TwigCsFixer\Rules\BlockNameSpacingRule;
use TwigCsFixer\Rules\DelimiterSpacingRule;
use TwigCsFixer\Rules\EmptyLinesRule;
use TwigCsFixer\Rules\IndentRule;
use TwigCsFixer\Rules\OperatorNameSpacingRule;
use TwigCsFixer\Rules\OperatorSpacingRule;
use TwigCsFixer\Rules\PunctuationSpacingRule;
use TwigCsFixer\Rules\TrailingCommaSingleLineRule;
use TwigCsFixer\Rules\TrailingSpaceRule;
use TwigCsFixer\Standard\Generic;

final class GenericTest extends TestCase
{
    public function testGetRules(): void
    {
        $standard = new Generic();

        static::assertEquals([
            new DelimiterSpacingRule(),
            new OperatorNameSpacingRule(),
            new OperatorSpacingRule(),
            new PunctuationSpacingRule(),
            new BlankEOFRule(),
            new BlockNameSpacingRule(),
            new EmptyLinesRule(),
            new IndentRule(),
            new TrailingCommaSingleLineRule(),
            new TrailingSpaceRule(),
        ], $standard->getRules());
    }
}
