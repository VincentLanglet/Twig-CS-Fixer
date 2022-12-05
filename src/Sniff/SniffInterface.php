<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;

interface SniffInterface
{
    /**
     * Messages will be added to the given `$report` object.
     *
     * @param list<Token> $stream
     */
    public function lintFile(array $stream, Report $report): void;

    /**
     * @param list<Token> $stream
     */
    public function fixFile(array $stream, FixerInterface $fixer): void;
}
