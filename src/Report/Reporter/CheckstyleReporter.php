<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;

final class CheckstyleReporter implements ReporterInterface
{
    public const NAME = 'checkstyle';

    public function display(OutputInterface $output, Report $report, ?string $level = null): void
    {
        $text = '<?xml version="1.0" encoding="UTF-8"?>'."\n";

        $text .= '<checkstyle>'."\n";

        foreach ($report->getFiles() as $file) {
            $fileMessages = $report->getMessages($file, $level);
            if (0 === \count($fileMessages)) {
                continue;
            }

            $text .= sprintf('  <file name="%s">', $this->xmlEncode($file))."\n";
            foreach ($fileMessages as $message) {
                $line = (string) $message->getLine();
                $linePosition = (string) $message->getLinePosition();
                $sniffName = $message->getSniffName();

                $text .= '    <error';
                if ('' !== $line) {
                    $text .= ' line="'.$line.'"';
                }
                if ('' !== $linePosition) {
                    $text .= ' column="'.$linePosition.'"';
                }
                $text .= ' severity="'.strtolower(SniffViolation::getLevelAsString($message->getLevel())).'"';
                $text .= ' message="'.$this->xmlEncode($message->getMessage()).'"';
                if (null !== $sniffName) {
                    $text .= ' source="'.$sniffName.'"';
                }
                $text .= '/>'."\n";
            }
            $text .= '  </file>'."\n";
        }

        $text .= '</checkstyle>';

        $output->writeln($text);
    }

    private function xmlEncode(string $data): string
    {
        return htmlspecialchars($data, \ENT_XML1 | \ENT_QUOTES);
    }
}
