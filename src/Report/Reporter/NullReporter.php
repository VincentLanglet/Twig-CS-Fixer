<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;

/**
 * Reporter without output.
 */
final class NullReporter implements ReporterInterface
{
    public const NAME = 'null';

    public function getName(): string
    {
        return self::NAME;
    }

    public function display(
        OutputInterface $output,
        Report $report,
        ?string $level,
        bool $debug,
    ): void {
    }
}
