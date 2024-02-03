<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\GithubReporter;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;

final class GithubReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level, bool $debug): void
    {
        $textFormatter = new GithubReporter();

        $file = __DIR__.'/Fixtures/file.twig';
        $report = new Report([new \SplFileInfo($file)]);

        $violation0 = new Violation(
            Violation::LEVEL_NOTICE,
            'Notice',
            $file,
            'Rule',
            new ViolationId('NoticeId', null, 1)
        );
        $report->addViolation($violation0);
        $violation1 = new Violation(
            Violation::LEVEL_WARNING,
            'Warning',
            $file,
            'Rule',
            new ViolationId('WarningId', null, 2, 22)
        );
        $report->addViolation($violation1);
        $violation2 = new Violation(
            Violation::LEVEL_ERROR,
            'Error',
            $file,
            'Rule',
            new ViolationId('ErrorId', null, 3, 33)
        );
        $report->addViolation($violation2);
        $violation3 = new Violation(
            Violation::LEVEL_FATAL,
            'Fatal'."\n".'with new line',
            $file,
            'Rule',
            new ViolationId('FatalId')
        );
        $report->addViolation($violation3);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, $level, $debug);

        $text = $output->fetch();
        static::assertStringContainsString($expected, $text);
    }

    /**
     * @return iterable<array-key, array{string, string|null, bool}>
     */
    public static function displayDataProvider(): iterable
    {
        yield [
            sprintf(
                <<<EOD
                    ::notice file=%1\$s/Fixtures/file.twig,line=1::Notice
                    ::warning file=%1\$s/Fixtures/file.twig,line=2,col=22::Warning
                    ::error file=%1\$s/Fixtures/file.twig,line=3,col=33::Error
                    ::error file=%1\$s/Fixtures/file.twig::Fatal%%0Awith new line
                    EOD,
                __DIR__
            ),
            null,
            false,
        ];
        yield [
            sprintf(
                <<<EOD
                    ::notice file=%1\$s/Fixtures/file.twig,line=1::NoticeId:1
                    ::warning file=%1\$s/Fixtures/file.twig,line=2,col=22::WarningId:2:22
                    ::error file=%1\$s/Fixtures/file.twig,line=3,col=33::ErrorId:3:33
                    ::error file=%1\$s/Fixtures/file.twig::FatalId
                    EOD,
                __DIR__
            ),
            null,
            true,
        ];
    }
}
