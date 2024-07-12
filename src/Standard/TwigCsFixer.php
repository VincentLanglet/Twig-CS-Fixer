<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\Delimiter\BlockNameSpacingRule;
use TwigCsFixer\Rules\Function\IncludeFunctionRule;
use TwigCsFixer\Rules\Punctuation\TrailingCommaSingleLineRule;
use TwigCsFixer\Rules\String\HashQuoteRule;
use TwigCsFixer\Rules\String\SingleQuoteRule;
use TwigCsFixer\Rules\Whitespace\BlankEOFRule;
use TwigCsFixer\Rules\Whitespace\EmptyLinesRule;
use TwigCsFixer\Rules\Whitespace\IndentRule;
use TwigCsFixer\Rules\Whitespace\TrailingSpaceRule;

/**
 * Default standard from this fixer.
 */
final class TwigCsFixer implements StandardInterface
{
    public function getRules(): array
    {
        return [
            ...(new Twig())->getRules(),
            new BlankEOFRule(),
            new BlockNameSpacingRule(),
            new EmptyLinesRule(),
            new HashQuoteRule(),
            new IncludeFunctionRule(),
            new IndentRule(),
            new SingleQuoteRule(),
            new TrailingCommaSingleLineRule(),
            new TrailingSpaceRule(),
        ];
    }
}
