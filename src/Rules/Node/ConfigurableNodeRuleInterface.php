<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

interface ConfigurableNodeRuleInterface extends NodeRuleInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array;
}
