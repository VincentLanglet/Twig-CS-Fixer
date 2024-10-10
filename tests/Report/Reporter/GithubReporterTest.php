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
use TwigCsFixer\Test\TestHelper;

final class GithubReporterTest extends TestCase
{
    public function testGetName(): void
    {
        static::assertSame(GithubReporter::NAME, (new GithubReporter())->getName());
    }

    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level, bool $debug): void
    {
        $textFormatter = new GithubReporter();

        $file = TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig');
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
        static::assertSame($expected, rtrim($text));
    }

    /**
     * @return iterable<array-key, array{string, string|null, bool}>
     */
    public static function displayDataProvider(): iterable
    {
        yield [
            \sprintf(
                <<<EOD
                    ::notice file=%1\$s,line=1::Notice
                    ::warning file=%1\$s,line=2,col=22::Warning
                    ::error file=%1\$s,line=3,col=33::Error
                    ::error file=%1\$s::Fatal%%0Awith new line
                    EOD,
                TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig'),
            ),
            null,
            false,
        ];
        yield [
            \sprintf(
                <<<EOD
                    ::notice file=%1\$s,line=1::NoticeId:1 -- Notice
                    ::warning file=%1\$s,line=2,col=22::WarningId:2:22 -- Warning
                    ::error file=%1\$s,line=3,col=33::ErrorId:3:33 -- Error
                    ::error file=%1\$s::FatalId -- Fatal%%0Awith new line
                    EOD,
                TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig'),
            ),
            null,
            true,
        ];
    }
}
