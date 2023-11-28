<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;

final class ReportTest extends TestCase
{
    public function testReportDefaultState(): void
    {
        $report = new Report([]);

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

        $report = new Report([
            new SplFileInfo($file),
            new SplFileInfo($file2),
            new SplFileInfo($file3),
        ]);

        $sniffViolation1 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice', $file);
        $sniffViolation2 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file);
        $sniffViolation3 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file2);
        $sniffViolation4 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file);
        $sniffViolation5 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file2);
        $sniffViolation6 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file3);
        $sniffViolation7 = new SniffViolation(SniffViolation::LEVEL_FATAL, 'Fatal', $file);

        $report->addViolation($sniffViolation1);
        $report->addViolation($sniffViolation2);
        $report->addViolation($sniffViolation3);
        $report->addViolation($sniffViolation4);
        $report->addViolation($sniffViolation5);
        $report->addViolation($sniffViolation6);
        $report->addViolation($sniffViolation7);

        static::assertSame(1, $report->getTotalNotices());
        static::assertSame(2, $report->getTotalWarnings());
        static::assertSame(4, $report->getTotalErrors());
        static::assertSame([$file, $file2, $file3], $report->getFiles());
        static::assertSame(3, $report->getTotalFiles());

        static::assertSame(
            [$sniffViolation1, $sniffViolation2, $sniffViolation4, $sniffViolation7],
            $report->getFileViolations($file)
        );
        static::assertSame(
            [$sniffViolation3, $sniffViolation5],
            $report->getFileViolations($file2)
        );
        static::assertSame(
            [$sniffViolation6],
            $report->getFileViolations($file3)
        );

        static::assertSame(
            [
                $sniffViolation1,
                $sniffViolation2,
                $sniffViolation4,
                $sniffViolation7,
                $sniffViolation3,
                $sniffViolation5,
                $sniffViolation6,
            ],
            $report->getViolations()
        );

        static::assertSame(
            [$sniffViolation4, $sniffViolation7],
            $report->getFileViolations($file, Report::MESSAGE_TYPE_ERROR)
        );
        static::assertSame(
            [$sniffViolation5],
            $report->getFileViolations($file2, Report::MESSAGE_TYPE_ERROR)
        );
        static::assertSame(
            [$sniffViolation6],
            $report->getFileViolations($file3, Report::MESSAGE_TYPE_ERROR)
        );
        static::assertSame(
            [
                $sniffViolation4,
                $sniffViolation7,
                $sniffViolation5,
                $sniffViolation6,
            ],
            $report->getViolations(Report::MESSAGE_TYPE_ERROR)
        );
    }

    public function testAddViolationForAnotherFile(): void
    {
        $report = new Report([new SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->addViolation(new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Message', 'another_file.twig'));
    }

    public function testGetViolationForAnotherFile(): void
    {
        $report = new Report([new SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->getFileViolations('another_file.twig');
    }

    public function testAddFixedFile(): void
    {
        $report = new Report([new SplFileInfo('file.twig')]);

        static::assertSame([], $report->getFixedFiles());
        $report->addFixedFile('file.twig');
        static::assertSame(['file.twig'], $report->getFixedFiles());
    }

    public function testAddFixedFileForAnotherFile(): void
    {
        $report = new Report([new SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->addFixedFile('another_file.twig');
    }
}
