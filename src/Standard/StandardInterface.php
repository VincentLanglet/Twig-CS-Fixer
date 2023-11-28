<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\RuleInterface;

interface StandardInterface
{
    /**
     * @return RuleInterface[]
     */
    public function getRules(): array;
}
