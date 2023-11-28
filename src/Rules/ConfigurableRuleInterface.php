<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

interface ConfigurableRuleInterface extends RuleInterface
{
    /**
     * @return array<mixed>
     */
    public function getConfiguration(): array;
}
