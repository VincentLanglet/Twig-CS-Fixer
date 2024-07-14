<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Tokens;

interface FixableRuleInterface
{
    /**
     * @param list<ViolationId> $ignoredViolations
     */
    public function fixFile(Tokens $tokens, FixerInterface $fixer, array $ignoredViolations = []): void;
}
