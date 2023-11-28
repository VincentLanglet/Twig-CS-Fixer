<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;

final class JUnitReporter implements ReporterInterface
{
    public const NAME = 'junit';

    public function display(OutputInterface $output, Report $report, ?string $level = null): void
    {
        $violations = $report->getViolations($level);
        $count = \count($violations);

        $text = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $text .= '<testsuites>'."\n";
        $text .= '  '.sprintf(
            '<testsuite name="Twig CS Fixer" tests="%d" failures="%d">',
            max($count, 1),
            $count
        )."\n";

        if ($count > 0) {
            foreach ($violations as $violation) {
                $text .= $this->createTestCase(
                    sprintf('%s:%s', $violation->getFilename(), $violation->getLine() ?? 0),
                    strtolower(Violation::getLevelAsString($violation->getLevel())),
                    $violation->getMessage()
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
