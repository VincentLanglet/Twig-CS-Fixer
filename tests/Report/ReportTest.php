<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;

class ReportTest extends TestCase
{
    public function testReportDefaultState(): void
    {
        $report = new Report();

        static::assertSame(0, $report->getTotalNotices());
        static::assertSame(0, $report->getTotalWarnings());
        static::assertSame(0, $report->getTotalErrors());
        static::assertSame([], $report->getFiles());
        static::assertSame(0, $report->getTotalFiles());
    }

    public function testReport(): void
    {
        $file = 'file.twig';
        $file2 = 'file2.twig';
        $file3 = 'file3.twig';

        $report = new Report();
        $report->addFile($file);
        $report->addFile($file2);
        $report->addFile($file3);

        $sniffViolation1 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice', $file);
        $sniffViolation2 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file);
        $sniffViolation3 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file2);
        $sniffViolation4 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file);
        $sniffViolation5 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file2);
        $sniffViolation6 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file3);
        $sniffViolation7 = new SniffViolation(SniffViolation::LEVEL_FATAL, 'Fatal', $file);

        $report->addMessage($sniffViolation1);
        $report->addMessage($sniffViolation2);
        $report->addMessage($sniffViolation3);
        $report->addMessage($sniffViolation4);
        $report->addMessage($sniffViolation5);
        $report->addMessage($sniffViolation6);
        $report->addMessage($sniffViolation7);

        static::assertSame(1, $report->getTotalNotices());
        static::assertSame(2, $report->getTotalWarnings());
        static::assertSame(4, $report->getTotalErrors());
        static::assertSame([$file, $file2, $file3], $report->getFiles());
        static::assertSame(3, $report->getTotalFiles());

        static::assertSame([
            $file  => [$sniffViolation1, $sniffViolation2, $sniffViolation4, $sniffViolation7],
            $file2 => [$sniffViolation3, $sniffViolation5],
            $file3 => [$sniffViolation6],
        ], $report->getMessagesByFiles());

        static::assertSame([
            $file  => [$sniffViolation4, $sniffViolation7],
            $file2 => [$sniffViolation5],
            $file3 => [$sniffViolation6],
        ], $report->getMessagesByFiles(Report::MESSAGE_TYPE_ERROR));
    }

    public function testAddMessageForAnotherFile(): void
    {
        $report = new Report();
        $report->addFile('file.twig');

        self::expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->addMessage(new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Message', 'another_file.twig'));
    }
}
