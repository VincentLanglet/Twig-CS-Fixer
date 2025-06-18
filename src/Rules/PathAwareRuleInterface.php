<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Token\Tokens;

interface PathAwareRuleInterface extends RuleInterface
{
    public function support(string $path): bool;
}
