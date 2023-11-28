<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\JUnitReporter;
use TwigCsFixer\Report\SniffViolation;

final class JUnitReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level): void
    {
        $textFormatter = new JUnitReporter();

        $file = __DIR__.'/Fixtures/file.twig';
        $file2 = __DIR__.'/Fixtures/file2.twig';
        $file3 = __DIR__.'/Fixtures/file3.twig';
        $report = new Report([new SplFileInfo($file), new SplFileInfo($file2), new SplFileInfo($file3)]);

        $violation0 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice', $file, 1, 11, 'NoticeSniff');
        $report->addViolation($violation0);
        $violation1 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file, 2, 22, 'WarningSniff');
        $report->addViolation($violation1);
        $violation2 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file, 3, 33, 'ErrorSniff');
        $report->addViolation($violation2);
        $violation3 = new SniffViolation(SniffViolation::LEVEL_FATAL, 'Fatal', $file);
        $report->addViolation($violation3);

        $violation4 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice2', $file2, 1, 11, 'Notice2Sniff');
        $report->addViolation($violation4);

        $violation5 = new SniffViolation(SniffViolation::LEVEL_FATAL, '\'"<&>"\'', $file3);
        $report->addViolation($violation5);

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
                    <?xml version="1.0" encoding="UTF-8"?>
                    <testsuites>
                      <testsuite name="Twig CS Fixer" tests="6" failures="6">
                        <testcase name="%1\$s/Fixtures/file.twig:1">
                          <failure type="notice" message="Notice" />
                        </testcase>
                        <testcase name="%1\$s/Fixtures/file.twig:2">
                          <failure type="warning" message="Warning" />
                        </testcase>
                        <testcase name="%1\$s/Fixtures/file.twig:3">
                          <failure type="error" message="Error" />
                        </testcase>
                        <testcase name="%1\$s/Fixtures/file.twig:0">
                          <failure type="fatal" message="Fatal" />
                        </testcase>
                        <testcase name="%1\$s/Fixtures/file2.twig:1">
                          <failure type="notice" message="Notice2" />
                        </testcase>
                        <testcase name="%1\$s/Fixtures/file3.twig:0">
                          <failure type="fatal" message="&apos;&quot;&lt;&amp;&gt;&quot;&apos;" />
                        </testcase>
                      </testsuite>
                    </testsuites>
                    EOD,
                __DIR__
            ),
            null,
        ];
    }

    public function testDisplaySuccess(): void
    {
        $textFormatter = new JUnitReporter();

        $file = __DIR__.'/Fixtures/file.twig';
        $file2 = __DIR__.'/Fixtures/file2.twig';
        $file3 = __DIR__.'/Fixtures/file3.twig';
        $report = new Report([new SplFileInfo($file), new SplFileInfo($file2), new SplFileInfo($file3)]);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report);

        $expected = <<<EOD
            <?xml version="1.0" encoding="UTF-8"?>
            <testsuites>
              <testsuite name="Twig CS Fixer" tests="1" failures="0">
                <testcase name="All OK">
                </testcase>
              </testsuite>
            </testsuites>
            EOD;

        $text = $output->fetch();
        static::assertStringContainsString($expected, $text);
    }
}
