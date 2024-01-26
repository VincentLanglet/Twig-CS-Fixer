<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Rules\Operator\OperatorNameSpacingRule;
use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Rules\Variable\VariableNameRule;

/**
 * Standard from twig.
 *
 * @see https://twig.symfony.com/doc/3.x/coding_standards.html
 */
final class Twig implements StandardInterface
{
    public function getRules(): array
    {
        return [
            new DelimiterSpacingRule(),
            new OperatorNameSpacingRule(),
            new OperatorSpacingRule(),
            new PunctuationSpacingRule(),
            new VariableNameRule(),
        ];
    }
}
