<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Human-readable output with context.
 */
final class TextFormatter
{
    private const ERROR_CURSOR_CHAR = '>>';
    private const ERROR_LINE_FORMAT = '%-5s| %s';
    private const ERROR_LINE_WIDTH = 120;

    private SymfonyStyle $io;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function display(Report $report, ?string $level = null): void
    {
        $messages = $report->getMessagesByFiles($level);

        foreach ($report->getFiles() as $file) {
            $fileMessages = $messages[$file];
            if (\count($fileMessages) > 0) {
                $this->io->text(sprintf('<fg=red>KO</fg=red> %s', $file));
            }

            $content = @file_get_contents($file);
            $rows = [];
            foreach ($fileMessages as $message) {
                $formattedText = [];
                $line = $message->getLine();

                if (null === $line || false === $content) {
                    $formattedText[] = $this->formatErrorMessage($message);
                } else {
                    $lines = $this->getContext($content, $line);
                    foreach ($lines as $no => $code) {
                        $formattedText[] = sprintf(
                            self::ERROR_LINE_FORMAT,
                            $no,
                            wordwrap($code, self::ERROR_LINE_WIDTH)
                        );

                        if ($no === $message->getLine()) {
                            $formattedText[] = $this->formatErrorMessage($message);
                        }
                    }
                }

                if (\count($rows) > 0) {
                    $rows[] = new TableSeparator();
                }

                $level = SniffViolation::getLevelAsString($message->getLevel());
                $rows[] = [
                    new TableCell(sprintf('<comment>%s</comment>', $level)),
                    implode("\n", $formattedText),
                ];
            }

            if (\count($rows) > 0) {
                $this->io->table([], $rows);
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
            $this->io->error($summaryString);
        } elseif (0 < $report->getTotalWarnings()) {
            $this->io->warning($summaryString);
        } else {
            $this->io->success($summaryString);
        }
    }

    /**
     * @return array<int, string>
     */
    private function getContext(string $template, int $line): array
    {
        $lines = explode("\n", $template);
        $position = max(0, $line - 2);
        $max = min(\count($lines), $line + 1);

        $result = [];
        $indents = [];

        do {
            preg_match('/^[\s\t]+/', $lines[$position], $match);
            $indents[] = \strlen($match[0] ?? '');
            $result[$position + 1] = $lines[$position];
            $position++;
        } while ($position < $max);

        return array_map(fn (string $code): string => substr($code, min($indents)), $result);
    }

    private function formatErrorMessage(SniffViolation $message): string
    {
        return sprintf(
            sprintf('<fg=red>%s</fg=red>', self::ERROR_LINE_FORMAT),
            self::ERROR_CURSOR_CHAR,
            wordwrap($message->getMessage(), self::ERROR_LINE_WIDTH)
        );
    }
}
