<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Token\Tokens;

interface RuleInterface
{
    /**
     * Messages will be added to the given `$report` object.
     *
     * @param list<ViolationId> $ignoredViolations
     */
    public function lintFile(Tokens $tokens, Report $report, array $ignoredViolations = []): void;
}
