<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Token\Token;

interface RuleInterface
{
    /**
     * Messages will be added to the given `$report` object.
     *
     * @param array<int, Token> $stream
     * @param list<ViolationId> $ignoredViolations
     */
    public function lintFile(array $stream, Report $report, array $ignoredViolations = []): void;
}
