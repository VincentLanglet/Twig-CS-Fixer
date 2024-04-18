<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Standard;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\Delimiter\BlockNameSpacingRule;
use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Rules\Operator\OperatorNameSpacingRule;
use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Rules\Punctuation\TrailingCommaSingleLineRule;
use TwigCsFixer\Rules\String\SingleQuoteRule;
use TwigCsFixer\Rules\Variable\VariableNameRule;
use TwigCsFixer\Rules\Whitespace\BlankEOFRule;
use TwigCsFixer\Rules\Whitespace\EmptyLinesRule;
use TwigCsFixer\Rules\Whitespace\IndentRule;
use TwigCsFixer\Rules\Whitespace\TrailingSpaceRule;
use TwigCsFixer\Standard\TwigCsFixer;

final class TwigCsFixerTest extends TestCase
{
    public function testGetRules(): void
    {
        $standard = new TwigCsFixer();

        static::assertEquals([
            new DelimiterSpacingRule(),
            new OperatorNameSpacingRule(),
            new OperatorSpacingRule(),
            new PunctuationSpacingRule(),
            new VariableNameRule(),
            new BlankEOFRule(),
            new BlockNameSpacingRule(),
            new EmptyLinesRule(),
            new IndentRule(),
            new SingleQuoteRule(),
            new TrailingCommaSingleLineRule(),
            new TrailingSpaceRule(),
        ], $standard->getRules());
    }
}
