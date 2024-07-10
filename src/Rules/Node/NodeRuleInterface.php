<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\NodeVisitor\NodeVisitorInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\ViolationId;

interface NodeRuleInterface extends NodeVisitorInterface
{
    /**
     * @param list<ViolationId> $ignoredViolations
     */
    public function enterFile(Report $report, string $filePath, array $ignoredViolations = []): void;
}
