<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Token\Token;

/**
 * Interface for all sniffs.
 */
interface SniffInterface
{
    /**
     * Once the sniff is enabled, it will be registered and executed when a template is tokenized or parsed.
     * Messages will be added to the given `$report` object.
     *
     * @param Report $report
     *
     * @return void
     */
    public function enableReport(Report $report): void;

    /**
     * @param Fixer $fixer
     *
     * @return void
     */
    public function enableFixer(Fixer $fixer): void;

    /**
     * It usually is disabled when the processing is over, it will reset the sniff internal values for next check.
     *
     * @return void
     */
    public function disable(): void;

    /**
     * @param Token[] $stream
     *
     * @return void
     */
    public function processFile(array $stream): void;
}
