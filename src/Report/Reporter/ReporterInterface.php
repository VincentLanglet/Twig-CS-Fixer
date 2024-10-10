<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;

interface ReporterInterface
{
    public function display(
        OutputInterface $output,
        Report $report,
        ?string $level,
        bool $debug,
    ): void;

    public function getName(): string;
}
