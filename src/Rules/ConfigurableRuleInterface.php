<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

interface ConfigurableRuleInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array;
}
