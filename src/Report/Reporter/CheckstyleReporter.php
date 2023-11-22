<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;

/**
 * Human-readable output with context.
 */
final class CheckstyleReporter implements ReporterInterface
{
    public const NAME = 'checkstyle';

    public function display(OutputInterface $output, Report $report, ?string $level = null): void
    {
        $checkstyle = new \SimpleXMLElement('<checkstyle version="1.0.0"/>');

        foreach ($report->getFiles() as $file) {
            $fileMessages = $report->getMessages($file, $level);
            if (\count($fileMessages) > 0) {
                /** @var \SimpleXMLElement $fileNode */
                $fileNode = $checkstyle->addChild('file');
                $fileNode->addAttribute('name', $file);

                foreach ($fileMessages as $message) {
                    /** @var \SimpleXMLElement $violation */
                    $violation = $fileNode->addChild('violation');
                    $violation->addAttribute('column', (string) $message->getLinePosition());
                    $violation->addAttribute('severity', strtolower(SniffViolation::getLevelAsString($message->getLevel())));
                    $violation->addAttribute('message', $message->getMessage());
                    $violation->addAttribute('source', $message->getSniffName() ?? '');
                }
            }
        }

        $output->writeln((string) $checkstyle->asXML());
    }
}
