<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Node;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\RuleTrait;
use Webmozart\Assert\Assert;

abstract class AbstractNodeRule implements NodeRuleInterface
{
    use RuleTrait;

    final public function setReport(Report $report, array $ignoredViolations = []): void
    {
        $this->report = $report;
        $this->ignoredViolations = $ignoredViolations;
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }

    final protected function addWarning(string $message, Node $node, ?string $messageId = null): bool
    {
        $templateName = $node->getTemplateName();
        Assert::notNull($templateName, 'Parsed node should always have a source context.');

        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $templateName,
            $node->getTemplateLine(),
            null,
            $messageId,
        );
    }

    final protected function addError(string $message, Node $node, ?string $messageId = null): bool
    {
        $templateName = $node->getTemplateName();
        Assert::notNull($templateName, 'Parsed node should always have a source context.');

        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $templateName,
            $node->getTemplateLine(),
            null,
            $messageId,
        );
    }
}
