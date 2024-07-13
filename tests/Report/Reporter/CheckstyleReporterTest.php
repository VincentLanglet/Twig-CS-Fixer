<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\CheckstyleReporter;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Tests\TestHelper;

final class CheckstyleReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level, bool $debug): void
    {
        $textFormatter = new CheckstyleReporter();

        $file = TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file.twig');
        $file2 = TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file2.twig');
        $file3 = TestHelper::getOsPath('tests/Report/Reporter/Fixtures/file3.twig');
        $report = new Report([new \SplFileInfo($file), new \SplFileInfo($file2), new \SplFileInfo($file3)]);

        $violation0 = new Violation(
            Violation::LEVEL_NOTICE,
            'Notice',
            $file,
            'NoticeRule',
            new ViolationId('NoticeId', null, 1)
        );
        $report->addViolation($violation0);
        $violation1 = new Violation(
            Violation::LEVEL_WARNING,
            'Warning',
            $file,
            'WarningRule',
            new ViolationId('WarningId', null, 2, 22)
        );
        $report->addViolation($violation1);
        $violation2 = new Violation(
            Violation::LEVEL_ERROR,
            'Error',
            $file,
            'ErrorRule',
            new ViolationId('ErrorId', null, 3, 33)
        );
        $report->addViolation($violation2);
        $violation3 = new Violation(
            Violation::LEVEL_FATAL,
            'Fatal',
            $file,
            null,
            new ViolationId('FatalId')
        );
        $report->addViolation($violation3);

        $violation4 = new Violation(
            Violation::LEVEL_NOTICE,
            'Notice2',
            $file2,
            'Notice2Rule',
            new ViolationId('NoticeId', null, 1)
        );
        $report->addViolation($violation4);

        $violation5 = new Violation(
            Violation::LEVEL_FATAL,
            '\'"<&>"\'',
            $file3,
            null,
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
                    <checkstyle>
                      <file name="%1\$s">
                        <error line="1" severity="notice" message="Notice" source="NoticeRule"/>
                        <error line="2" column="22" severity="warning" message="Warning" source="WarningRule"/>
                        <error line="3" column="33" severity="error" message="Error" source="ErrorRule"/>
                        <error severity="fatal" message="Fatal"/>
                      </file>
                      <file name="%2\$s">
                        <error line="1" severity="notice" message="Notice2" source="Notice2Rule"/>
                      </file>
                      <file name="%3\$s">
                        <error severity="fatal" message="&apos;&quot;&lt;&amp;&gt;&quot;&apos;"/>
                      </file>
                    </checkstyle>
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
                    <checkstyle>
                      <file name="%1\$s">
                        <error line="3" column="33" severity="error" message="Error" source="ErrorRule"/>
                        <error severity="fatal" message="Fatal"/>
                      </file>
                      <file name="%2\$s">
                        <error severity="fatal" message="&apos;&quot;&lt;&amp;&gt;&quot;&apos;"/>
                      </file>
                    </checkstyle>
                    EOD,
                TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig'),
                TestHelper::getOsPath(__DIR__.'/Fixtures/file3.twig'),
            ),
            Report::MESSAGE_TYPE_ERROR,
            false,
        ];

        yield [
            sprintf(
                <<<EOD
                    <?xml version="1.0" encoding="UTF-8"?>
                    <checkstyle>
                      <file name="%1\$s">
                        <error line="3" column="33" severity="error" message="ErrorId:3:33" source="ErrorRule"/>
                        <error severity="fatal" message="FatalId"/>
                      </file>
                      <file name="%2\$s">
                        <error severity="fatal" message="FatalId"/>
                      </file>
                    </checkstyle>
                    EOD,
                TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig'),
                TestHelper::getOsPath(__DIR__.'/Fixtures/file3.twig'),
            ),
            Report::MESSAGE_TYPE_ERROR,
            true,
        ];
    }
}
