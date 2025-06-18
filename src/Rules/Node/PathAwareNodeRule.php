<?php

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Node;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Rules\ConfigurableRuleInterface;

final class PathAwareNodeRule implements PathAwareNodeRuleInterface, ConfigurableRuleInterface
{
    public function __construct(
        private NodeRuleInterface $rule,
        private string $filePattern,
    ) {
    }

    public function getConfiguration(): array
    {
        $config = $this->rule instanceof ConfigurableRuleInterface ? $this->rule->getConfiguration() : [];
        $config['__class'] = $this->rule::class;
        $config['__file_pattern'] = $this->filePattern;

        return $config;
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        return $this->rule->enterNode($node, $env);
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        return $this->rule->leaveNode($node, $env);
    }

    public function getPriority(): int
    {
        return $this->rule->getPriority();
    }

    public function setReport(Report $report, array $ignoredViolations = []): void
    {
        $this->rule->setReport($report, $ignoredViolations);
    }

    public function support(string $path): bool
    {
        return fnmatch($this->filePattern, $path);
    }
}
