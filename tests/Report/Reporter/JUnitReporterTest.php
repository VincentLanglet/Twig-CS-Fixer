<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\JUnitReporter;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Tests\TestHelper;

final class JUnitReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level, bool $debug): void
    {
        $textFormatter = new JUnitReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig');
        $file2 = TestHelper::getOsPath(__DIR__.'/Fixtures/file2.twig');
        $file3 = TestHelper::getOsPath(__DIR__.'/Fixtures/file3.twig');
        $report = new Report([new \SplFileInfo($file), new \SplFileInfo($file2), new \SplFileInfo($file3)]);

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
            'Fatal',
            $file,
            'Rule',
            new ViolationId('FatalId')
        );
        $report->addViolation($violation3);

        $violation4 = new Violation(
            Violation::LEVEL_NOTICE,
            'Notice2',
            $file2,
            'Rule',
            new ViolationId('NoticeId', null, 1)
        );
        $report->addViolation($violation4);

        $violation5 = new Violation(
            Violation::LEVEL_FATAL,
            '\'"<&>"\'',
            $file3,
            'Rule',
            new ViolationId('FatalId')
        );
        $report->addViolation($violation5);

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
            sprintf(
                <<<EOD
                    <?xml version="1.0" encoding="UTF-8"?>
                    <testsuites>
                      <testsuite name="Twig CS Fixer" tests="6" failures="6">
                        <testcase name="%1\$s:1">
                          <failure type="notice" message="Notice" />
                        </testcase>
                        <testcase name="%1\$s:2">
                          <failure type="warning" message="Warning" />
                        </testcase>
                        <testcase name="%1\$s:3">
                          <failure type="error" message="Error" />
                        </testcase>
                        <testcase name="%1\$s:0">
                          <failure type="fatal" message="Fatal" />
                        </testcase>
                        <testcase name="%2\$s:1">
                          <failure type="notice" message="Notice2" />
                        </testcase>
                        <testcase name="%3\$s:0">
                          <failure type="fatal" message="&apos;&quot;&lt;&amp;&gt;&quot;&apos;" />
                        </testcase>
                      </testsuite>
                    </testsuites>
                    EOD,
                TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig'),
                TestHelper::getOsPath(__DIR__.'/Fixtures/file2.twig'),
                TestHelper::getOsPath(__DIR__.'/Fixtures/file3.twig'),
            ),
            null,
            false,
        ];
        yield [
            sprintf(
                <<<EOD
                    <?xml version="1.0" encoding="UTF-8"?>
                    <testsuites>
                      <testsuite name="Twig CS Fixer" tests="6" failures="6">
                        <testcase name="%1\$s:1">
                          <failure type="notice" message="NoticeId:1" />
                        </testcase>
                        <testcase name="%1\$s:2">
                          <failure type="warning" message="WarningId:2:22" />
                        </testcase>
                        <testcase name="%1\$s:3">
                          <failure type="error" message="ErrorId:3:33" />
                        </testcase>
                        <testcase name="%1\$s:0">
                          <failure type="fatal" message="FatalId" />
                        </testcase>
                        <testcase name="%2\$s:1">
                          <failure type="notice" message="NoticeId:1" />
                        </testcase>
                        <testcase name="%3\$s:0">
                          <failure type="fatal" message="FatalId" />
                        </testcase>
                      </testsuite>
                    </testsuites>
                    EOD,
                TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig'),
                TestHelper::getOsPath(__DIR__.'/Fixtures/file2.twig'),
                TestHelper::getOsPath(__DIR__.'/Fixtures/file3.twig'),
            ),
            null,
            true,
        ];
    }

    public function testDisplaySuccess(): void
    {
        $textFormatter = new JUnitReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig');
        $file2 = TestHelper::getOsPath(__DIR__.'/Fixtures/file2.twig');
        $file3 = TestHelper::getOsPath(__DIR__.'/Fixtures/file3.twig');
        $report = new Report([new \SplFileInfo($file), new \SplFileInfo($file2), new \SplFileInfo($file3)]);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, null, false);

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
        static::assertSame($expected, rtrim($text));
    }
}
