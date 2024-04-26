<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\RuleInterface;

interface StandardInterface
{
    /**
     * @return list<RuleInterface>
     */
    public function getRules(): array;
}
