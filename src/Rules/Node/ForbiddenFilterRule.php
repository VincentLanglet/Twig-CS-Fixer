<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use TwigCsFixer\Rules\ConfigurableRuleInterface;

/**
 * Ensures some filters are not used.
 */
final class ForbiddenFilterRule extends AbstractNodeRule implements ConfigurableRuleInterface
{
    /**
     * @param list<string> $filters
     */
    public function __construct(
        private array $filters,
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'filters' => $this->filters,
        ];
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        if (!$node instanceof FilterExpression) {
            return $node;
        }

        $filterName = $node->hasAttribute('name')
            ? $node->getAttribute('name')
        // @codeCoverageIgnoreStart
            : $node->getNode('filter')->getAttribute('value'); // BC for twig/twig < 3.12
        // @codeCoverageIgnoreEnd
        if (!\in_array($filterName, $this->filters, true)) {
            return $node;
        }

        $this->addError(
            \sprintf('Filter "%s" is not allowed.', $filterName),
            $node,
        );

        return $node;
    }
}
