<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Node;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\RuleTrait;

abstract class AbstractNodeRule implements NodeRuleInterface
{
    use RuleTrait;

    private ?string $filePath = null;

    public function getName(): string
    {
        return static::class;
    }

    public function getShortName(): string
    {
        $shortName = (new \ReflectionClass($this))->getShortName();

        return str_ends_with($shortName, 'Rule') ? substr($shortName, 0, -4) : $shortName;
    }

    public function enterFile(Report $report, string $filePath, array $ignoredViolations = []): void
    {
        $this->report = $report;
        $this->filePath = $filePath;
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

    protected function addWarning(string $message, Node $node, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $node->getTemplateName(),
            $node->getTemplateLine(),
            null,
            $messageId,
        );
    }

    protected function addFileWarning(string $message, Node $node, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $node->getTemplateName(),
            $node->getTemplateLine(),
            null,
            $messageId,
        );
    }

    protected function addError(string $message, Node $node, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $node->getTemplateName(),
            $node->getTemplateLine(),
            null,
            $messageId,
        );
    }
}
