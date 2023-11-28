<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\DelimiterSpacingRule;
use TwigCsFixer\Rules\OperatorNameSpacingRule;
use TwigCsFixer\Rules\OperatorSpacingRule;
use TwigCsFixer\Rules\PunctuationSpacingRule;

/**
 * Standard from twig.
 *
 * @see https://twig.symfony.com/doc/3.x/coding_standards.html
 */
class Twig implements StandardInterface
{
    public function getRules(): array
    {
        return [
            new DelimiterSpacingRule(),
            new OperatorNameSpacingRule(),
            new OperatorSpacingRule(),
            new PunctuationSpacingRule(),
        ];
    }
}
