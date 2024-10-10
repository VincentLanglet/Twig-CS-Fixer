<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;

final class CheckstyleReporter implements ReporterInterface
{
    public const NAME = 'checkstyle';

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
        $text = '<?xml version="1.0" encoding="UTF-8"?>'.\PHP_EOL;

        $text .= '<checkstyle>'.\PHP_EOL;

        foreach ($report->getFiles() as $file) {
            $fileViolations = $report->getFileViolations($file, $level);
            if (0 === \count($fileViolations)) {
                continue;
            }

            $realPath = $report->getRealPath($file);
            $text .= \sprintf('  <file name="%s">', $this->xmlEncode($realPath)).\PHP_EOL;
            foreach ($fileViolations as $violation) {
                $line = (string) $violation->getLine();
                $linePosition = (string) $violation->getLinePosition();
                $ruleName = $violation->getRuleName();

                $text .= '    <error';
                if ('' !== $line) {
                    $text .= ' line="'.$line.'"';
                }
                if ('' !== $linePosition) {
                    $text .= ' column="'.$linePosition.'"';
                }
                $text .= ' severity="'.strtolower(Violation::getLevelAsString($violation->getLevel())).'"';
                $text .= ' message="'.$this->xmlEncode($violation->getDebugMessage($debug)).'"';
                if (null !== $ruleName) {
                    $text .= ' source="'.$ruleName.'"';
                }
                $text .= '/>'.\PHP_EOL;
            }
            $text .= '  </file>'.\PHP_EOL;
        }

        $text .= '</checkstyle>';

        $output->writeln($text);
    }

    private function xmlEncode(string $data): string
    {
        return htmlspecialchars($data, \ENT_XML1 | \ENT_QUOTES);
    }
}
