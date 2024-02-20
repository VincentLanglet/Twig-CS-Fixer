<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;

/**
 * Human-readable output with context.
 */
final class TextReporter implements ReporterInterface
{
    public const NAME = 'text';

    private const ERROR_CURSOR_CHAR = '>>';
    private const ERROR_LINE_FORMAT = '%-5s| %s';
    private const ERROR_LINE_WIDTH = 120;

    public function display(
        OutputInterface $output,
        Report $report,
        ?string $level,
        bool $debug
    ): void {
        $io = new SymfonyStyle(new ArrayInput([]), $output);

        if (
            $io->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE
            && [] !== $report->getFixedFiles()
        ) {
            $io->text('Changed:');
            $io->listing($report->getFixedFiles());
        }

        foreach ($report->getFiles() as $file) {
            $fileViolations = $report->getFileViolations($file, $level);
            if (\count($fileViolations) > 0) {
                $io->text(sprintf('<fg=red>KO</fg=red> %s', $file));
            }

            $content = @file_get_contents($file);
            $rows = [];
            foreach ($fileViolations as $violation) {
                $formattedText = [];
                $line = $violation->getLine();

                if (null === $line || false === $content) {
                    $formattedText[] = $this->formatErrorMessage($violation, $debug);
                } else {
                    $lines = $this->getContext($content, $line);
                    foreach ($lines as $no => $code) {
                        $formattedText[] = sprintf(
                            self::ERROR_LINE_FORMAT,
                            $no,
                            wordwrap($code, self::ERROR_LINE_WIDTH, \PHP_EOL)
                        );

                        if ($no === $violation->getLine()) {
                            $formattedText[] = $this->formatErrorMessage($violation, $debug);
                        }
                    }
                }

                if (\count($rows) > 0) {
                    $rows[] = new TableSeparator();
                }

                $messageLevel = Violation::getLevelAsString($violation->getLevel());
                $rows[] = [
                    new TableCell(sprintf('<comment>%s</comment>', $messageLevel)),
                    implode(\PHP_EOL, $formattedText),
                ];
            }

            if (\count($rows) > 0) {
                $io->table([], $rows);
            }
        }

        $summaryString = sprintf(
            'Files linted: %d, notices: %d, warnings: %d, errors: %d',
            $report->getTotalFiles(),
            $report->getTotalNotices(),
            $report->getTotalWarnings(),
            $report->getTotalErrors()
        );

        if (0 < $report->getTotalErrors()) {
            $io->error($summaryString);
        } elseif (0 < $report->getTotalWarnings()) {
            $io->warning($summaryString);
        } else {
            $io->success($summaryString);
        }
    }

    /**
     * @return array<int, string>
     */
    private function getContext(string $template, int $line): array
    {
        $eol = FileHelper::detectEOL($template);
        $lines = explode($eol, $template);
        $position = max(0, $line - 2);
        $max = min(\count($lines), $line + 1);

        $result = [];
        $indents = [];

        do {
            preg_match('/^[\s\t]+/', $lines[$position], $match);
            $indents[] = \strlen($match[0] ?? '');
            $result[$position + 1] = $lines[$position];
            ++$position;
        } while ($position < $max);

        return array_map(fn (string $code): string => substr($code, min($indents)), $result);
    }

    private function formatErrorMessage(Violation $message, bool $debug): string
    {
        return sprintf(
            sprintf('<fg=red>%s</fg=red>', self::ERROR_LINE_FORMAT),
            self::ERROR_CURSOR_CHAR,
            wordwrap($message->getDebugMessage($debug), self::ERROR_LINE_WIDTH, \PHP_EOL)
        );
    }
}
