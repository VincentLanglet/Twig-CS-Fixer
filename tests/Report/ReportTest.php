<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;

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
            new \SplFileInfo($file),
            new \SplFileInfo($file2),
            new \SplFileInfo($file3),
        ]);

        $violation1 = new Violation(Violation::LEVEL_NOTICE, 'Notice', $file);
        $violation2 = new Violation(Violation::LEVEL_WARNING, 'Warning', $file);
        $violation3 = new Violation(Violation::LEVEL_WARNING, 'Warning', $file2);
        $violation4 = new Violation(Violation::LEVEL_ERROR, 'Error', $file);
        $violation5 = new Violation(Violation::LEVEL_ERROR, 'Error', $file2);
        $violation6 = new Violation(Violation::LEVEL_ERROR, 'Error', $file3);
        $violation7 = new Violation(Violation::LEVEL_FATAL, 'Fatal', $file);

        $report->addViolation($violation1);
        $report->addViolation($violation2);
        $report->addViolation($violation3);
        $report->addViolation($violation4);
        $report->addViolation($violation5);
        $report->addViolation($violation6);
        $report->addViolation($violation7);

        static::assertSame(1, $report->getTotalNotices());
        static::assertSame(2, $report->getTotalWarnings());
        static::assertSame(4, $report->getTotalErrors());
        static::assertSame([$file, $file2, $file3], $report->getFiles());
        static::assertSame(3, $report->getTotalFiles());

        static::assertSame(
            [$violation1, $violation2, $violation4, $violation7],
            $report->getFileViolations($file)
        );
        static::assertSame(
            [$violation3, $violation5],
            $report->getFileViolations($file2)
        );
        static::assertSame(
            [$violation6],
            $report->getFileViolations($file3)
        );

        static::assertSame(
            [
                $violation1,
                $violation2,
                $violation4,
                $violation7,
                $violation3,
                $violation5,
                $violation6,
            ],
            $report->getViolations()
        );

        static::assertSame(
            [$violation4, $violation7],
            $report->getFileViolations($file, Report::MESSAGE_TYPE_ERROR)
        );
        static::assertSame(
            [$violation5],
            $report->getFileViolations($file2, Report::MESSAGE_TYPE_ERROR)
        );
        static::assertSame(
            [$violation6],
            $report->getFileViolations($file3, Report::MESSAGE_TYPE_ERROR)
        );
        static::assertSame(
            [
                $violation4,
                $violation7,
                $violation5,
                $violation6,
            ],
            $report->getViolations(Report::MESSAGE_TYPE_ERROR)
        );
    }

    public function testAddViolationForAnotherFile(): void
    {
        $report = new Report([new \SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->addViolation(new Violation(Violation::LEVEL_NOTICE, 'Message', 'another_file.twig'));
    }

    public function testGetViolationForAnotherFile(): void
    {
        $report = new Report([new \SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->getFileViolations('another_file.twig');
    }

    public function testGetRealPathForAnotherFile(): void
    {
        $report = new Report([new \SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->getRealPath('another_file.twig');
    }

    public function testAddFixedFile(): void
    {
        $report = new Report([new \SplFileInfo('file.twig')]);

        static::assertSame([], $report->getFixedFiles());
        $report->addFixedFile('file.twig');
        static::assertSame(['file.twig'], $report->getFixedFiles());
    }

    public function testAddFixedFileForAnotherFile(): void
    {
        $report = new Report([new \SplFileInfo('file.twig')]);

        $this->expectExceptionMessage('The file "another_file.twig" is not handled by this report.');
        $report->addFixedFile('another_file.twig');
    }
}
