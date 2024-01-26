<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;

interface FixableRuleInterface
{
    /**
     * @param array<int, Token> $stream
     * @param list<ViolationId> $ignoredViolations
     */
    public function fixFile(array $stream, FixerInterface $fixer, array $ignoredViolations = []): void;
}
