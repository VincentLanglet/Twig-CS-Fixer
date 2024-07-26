<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use TwigCsFixer\Rules\ConfigurableRuleInterface;

/**
 * Ensures some functions are not used.
 */
final class ForbiddenFunctionRule extends AbstractNodeRule implements ConfigurableRuleInterface
{
    /**
     * @param list<string> $functions
     */
    public function __construct(
        private array $functions,
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'functions' => $this->functions,
        ];
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        if (!$node instanceof FunctionExpression) {
            return $node;
        }

        $functionName = $node->getAttribute('name');
        if (!\in_array($functionName, $this->functions, true)) {
            return $node;
        }

        $this->addError(
            \sprintf('Function "%s" is not allowed.', $functionName),
            $node,
        );

        return $node;
    }
}
