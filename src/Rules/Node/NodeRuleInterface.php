<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\NodeVisitor\NodeVisitorInterface;
use TwigCsFixer\Report\IgnoredViolationId;
use TwigCsFixer\Report\Report;

interface NodeRuleInterface extends NodeVisitorInterface
{
    /**
     * @param list<IgnoredViolationId> $ignoredViolations
     */
    public function setReport(Report $report, array $ignoredViolations = []): void;
}
