<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function explode;
use function file_get_contents;
use function implode;
use function max;
use function min;
use function preg_match;
use function sprintf;
use function strlen;
use function substr;
use function wordwrap;

/**
 * Human-readable output with context.
 */
final class TextFormatter
{
    private const ERROR_CURSOR_CHAR = '>>';
    private const ERROR_LINE_FORMAT = '%-5s| %s';
    private const ERROR_LINE_WIDTH  = 120;

    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param Report      $report
     * @param string|null $level
     *
     * @return void
     */
    public function display(Report $report, ?string $level = null): void
    {
        $messages = $report->getMessagesByFiles($level);

        foreach ($report->getFiles() as $file) {
            $fileMessages = $messages[$file];
            if (count($fileMessages) > 0) {
                $this->io->text('<fg=red>KO</fg=red> '.$file);
            }

            $content = file_get_contents($file);
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

                if (count($rows) > 0) {
                    $rows[] = new TableSeparator();
                }

                $level = SniffViolation::getLevelAsString($message->getLevel());
                $rows[] = [
                    new TableCell('<comment>'.$level.'</comment>'),
                    implode("\n", $formattedText),
                ];
            }

            if (count($rows) > 0) {
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
            $this->io->block($summaryString, 'ERROR', 'fg=black;bg=red', ' ', true);
        } elseif (0 < $report->getTotalWarnings()) {
            $this->io->block($summaryString, 'WARNING', 'fg=black;bg=yellow', ' ', true);
        } else {
            $this->io->block($summaryString, 'SUCCESS', 'fg=black;bg=green', ' ', true);
        }
    }

    /**
     * @param string $template
     * @param int    $line
     *
     * @return array<int, string>
     */
    private function getContext(string $template, int $line): array
    {
        $lines = explode("\n", $template);
        $position = max(0, $line - 2);
        $max = min(count($lines), $line + 1);

        $result = [];
        $indentCount = null;

        do {
            if (1 !== preg_match('/^[\s\t]+/', $lines[$position], $match)) {
                $indentCount = 0;
            } elseif (null === $indentCount || strlen($match[0]) < $indentCount) {
                $indentCount = strlen($match[0]);
            }

            $result[$position + 1] = $lines[$position];
            $position++;
        } while ($position < $max);

        foreach ($result as $index => $code) {
            $result[$index] = substr($code, $indentCount);
        }

        return $result;
    }

    /**
     * @param SniffViolation $message
     *
     * @return string
     */
    private function formatErrorMessage(SniffViolation $message): string
    {
        return sprintf(
            sprintf('<fg=red>%s</fg=red>', self::ERROR_LINE_FORMAT),
            self::ERROR_CURSOR_CHAR,
            wordwrap($message->getMessage(), self::ERROR_LINE_WIDTH)
        );
    }
}
