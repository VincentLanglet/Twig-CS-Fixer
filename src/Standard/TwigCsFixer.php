<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\Delimiter\BlockNameSpacingRule;
use TwigCsFixer\Rules\Function\IncludeFunctionRule;
use TwigCsFixer\Rules\Literal\CompactHashRule;
use TwigCsFixer\Rules\Literal\HashQuoteRule;
use TwigCsFixer\Rules\Literal\SingleQuoteRule;
use TwigCsFixer\Rules\Punctuation\TrailingCommaMultiLineRule;
use TwigCsFixer\Rules\Punctuation\TrailingCommaSingleLineRule;
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
            new CompactHashRule(),
            new HashQuoteRule(),
            new IncludeFunctionRule(),
            new IndentRule(),
            new SingleQuoteRule(),
            new TrailingCommaMultiLineRule(),
            new TrailingCommaSingleLineRule(),
            new TrailingSpaceRule(),
        ];
    }
}
