<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\Node\NodeRuleInterface;
use TwigCsFixer\Rules\RuleInterface;

interface StandardInterface
{
    /**
     * @return list<RuleInterface|NodeRuleInterface>
     */
    public function getRules(): array;
}
