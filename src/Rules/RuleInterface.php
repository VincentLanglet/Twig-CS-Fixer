<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Token\Tokens;

interface RuleInterface
{
    /**
     * Messages will be added to the given `$report` object.
     */
    public function lintFile(Tokens $tokens, Report $report): void;
}
