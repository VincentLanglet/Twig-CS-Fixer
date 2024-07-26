<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Node;
use TwigCsFixer\Rules\ConfigurableRuleInterface;

/**
 * Ensures some blocks are not used.
 */
final class ForbiddenBlockRule extends AbstractNodeRule implements ConfigurableRuleInterface
{
    /**
     * @param list<string> $blocks
     */
    public function __construct(
        private array $blocks,
    ) {
    }

    public function getConfiguration(): array
    {
        return [
            'blocks' => $this->blocks,
        ];
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        $blockName = $node->getNodeTag();
        if (null === $blockName) {
            return $node;
        }

        if (!\in_array($blockName, $this->blocks, true)) {
            return $node;
        }

        $this->addError(
            \sprintf('Block "%s" is not allowed.', $blockName),
            $node,
        );

        return $node;
    }
}
