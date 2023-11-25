<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;

final class JUnitReporter implements ReporterInterface
{
    public const NAME = 'junit';

    public function display(OutputInterface $output, Report $report, ?string $level = null): void
    {
        $messages = $report->getAllMessages($level);
        $totalErrors = \count($messages);

        $text = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $text .= '<testsuites>'."\n";
        $text .= '  '.sprintf(
            '<testsuite name="Twig CS Fixer" tests="%d" failures="%d">',
            max($totalErrors, 1),
            $totalErrors
        )."\n";

        if ($totalErrors > 0) {
            foreach ($messages as $message) {
                $text .= $this->createTestCase(
                    sprintf('%s:%s', $message->getFilename(), $message->getLine() ?? 0),
                    strtolower(SniffViolation::getLevelAsString($message->getLevel())),
                    $message->getMessage()
                );
            }
        } else {
            $text .= $this->createTestCase('All OK');
        }

        $text .= '  </testsuite>'."\n";
        $text .= '</testsuites>';

        $output->writeln($text);
    }

    private function createTestCase(string $name, string $type = '', ?string $message = null): string
    {
        $result = '    '.sprintf('<testcase name="%s">', $this->xmlEncode($name))."\n";

        if (null !== $message) {
            $result .= '      '
                .sprintf('<failure type="%s" message="%s" />', $this->xmlEncode($type), $this->xmlEncode($message))
                ."\n";
        }

        $result .= '    </testcase>'."\n";

        return $result;
    }

    private function xmlEncode(string $data): string
    {
        return htmlspecialchars($data, \ENT_XML1 | \ENT_QUOTES);
    }
}
