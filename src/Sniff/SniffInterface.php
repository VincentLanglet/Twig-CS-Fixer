<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Token\Token;

interface SniffInterface
{
    /**
     * Once the sniff is enabled, it will be registered and executed when a template is tokenized or parsed.
     * Messages will be added to the given `$report` object.
     */
    public function enableReport(Report $report): void;

    public function enableFixer(Fixer $fixer): void;

    /**
     * It is usually disabled when the processing is over, it will reset the sniff internal values for next check.
     */
    public function disable(): void;

    /**
     * @param list<Token> $stream
     */
    public function processFile(array $stream): void;
}
