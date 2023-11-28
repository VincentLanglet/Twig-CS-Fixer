<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\BlankEOFRule;
use TwigCsFixer\Rules\BlockNameSpacingRule;
use TwigCsFixer\Rules\EmptyLinesRule;
use TwigCsFixer\Rules\IndentRule;
use TwigCsFixer\Rules\TrailingCommaSingleLineRule;
use TwigCsFixer\Rules\TrailingSpaceRule;

/**
 * Default standard from this fixer.
 */
final class Generic implements StandardInterface
{
    public function getRules(): array
    {
        return [
            ...(new Twig())->getRules(),
            new BlankEOFRule(),
            new BlockNameSpacingRule(),
            new EmptyLinesRule(),
            new IndentRule(),
            new TrailingCommaSingleLineRule(),
            new TrailingSpaceRule(),
        ];
    }
}
