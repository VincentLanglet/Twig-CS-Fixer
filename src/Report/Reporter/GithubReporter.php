<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;

/**
 * Allow errors to be reported in pull-requests diff when run in a GitHub Action.
 *
 * @see https://help.github.com/en/actions/reference/workflow-commands-for-github-actions#setting-an-error-message
 */
final class GithubReporter implements ReporterInterface
{
    public const NAME = 'github';

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
        $violations = $report->getViolations($level);
        foreach ($violations as $violation) {
            $text = match ($violation->getLevel()) {
                Violation::LEVEL_NOTICE => '::notice',
                Violation::LEVEL_WARNING => '::warning',
                default => '::error',
            };

            $text .= ' file='.$violation->getFilename();

            $line = (string) $violation->getLine();
            if ('' !== $line) {
                $text .= ',line='.$line;
            }
            $linePosition = (string) $violation->getLinePosition();
            if ('' !== $linePosition) {
                $text .= ',col='.$linePosition;
            }

            // newlines need to be encoded
            // see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
            $text .= '::'.str_replace("\n", '%0A', $violation->getDebugMessage($debug));

            $output->writeln($text);
        }
    }
}
