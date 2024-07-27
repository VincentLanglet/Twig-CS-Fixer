<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Tokens;

interface FixableRuleInterface
{
    public function fixFile(Tokens $tokens, FixerInterface $fixer): void;
}
