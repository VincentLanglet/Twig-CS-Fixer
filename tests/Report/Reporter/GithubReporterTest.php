<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\GithubReporter;
use TwigCsFixer\Report\SniffViolation;

final class GithubReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level): void
    {
        $textFormatter = new GithubReporter();

        $file = __DIR__.'/Fixtures/file.twig';
        $report = new Report([new SplFileInfo($file)]);

        $violation0 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice', $file, 1, 11, 'NoticeSniff');
        $report->addViolation($violation0);
        $violation1 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file, 2, 22, 'WarningSniff');
        $report->addViolation($violation1);
        $violation2 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file, 3, 33, 'ErrorSniff');
        $report->addViolation($violation2);
        $violation3 = new SniffViolation(SniffViolation::LEVEL_FATAL, 'Fatal'."\n".'with new line', $file);
        $report->addViolation($violation3);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, $level);

        $text = $output->fetch();
        static::assertStringContainsString($expected, $text);
    }

    /**
     * @return iterable<array-key, array{string, string|null}>
     */
    public static function displayDataProvider(): iterable
    {
        yield [
            sprintf(
                <<<EOD
                    ::notice file=%1\$s/Fixtures/file.twig,line=1,col=11::Notice
                    ::warning file=%1\$s/Fixtures/file.twig,line=2,col=22::Warning
                    ::error file=%1\$s/Fixtures/file.twig,line=3,col=33::Error
                    ::error file=%1\$s/Fixtures/file.twig::Fatal%%0Awith new line
                    EOD,
                __DIR__
            ),
            null,
        ];
    }
}
