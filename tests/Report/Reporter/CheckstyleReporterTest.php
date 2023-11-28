<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\CheckstyleReporter;
use TwigCsFixer\Report\Violation;

final class CheckstyleReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(string $expected, ?string $level): void
    {
        $textFormatter = new CheckstyleReporter();

        $file = __DIR__.'/Fixtures/file.twig';
        $file2 = __DIR__.'/Fixtures/file2.twig';
        $file3 = __DIR__.'/Fixtures/file3.twig';
        $report = new Report([new SplFileInfo($file), new SplFileInfo($file2), new SplFileInfo($file3)]);

        $violation0 = new Violation(Violation::LEVEL_NOTICE, 'Notice', $file, 1, 11, 'NoticeRule');
        $report->addViolation($violation0);
        $violation1 = new Violation(Violation::LEVEL_WARNING, 'Warning', $file, 2, 22, 'WarningRule');
        $report->addViolation($violation1);
        $violation2 = new Violation(Violation::LEVEL_ERROR, 'Error', $file, 3, 33, 'ErrorRule');
        $report->addViolation($violation2);
        $violation3 = new Violation(Violation::LEVEL_FATAL, 'Fatal', $file);
        $report->addViolation($violation3);

        $violation4 = new Violation(Violation::LEVEL_NOTICE, 'Notice2', $file2, 1, 11, 'Notice2Rule');
        $report->addViolation($violation4);

        $violation5 = new Violation(Violation::LEVEL_FATAL, '\'"<&>"\'', $file3);
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
                    <checkstyle>
                      <file name="%1\$s/Fixtures/file.twig">
                        <error line="1" column="11" severity="notice" message="Notice" source="NoticeRule"/>
                        <error line="2" column="22" severity="warning" message="Warning" source="WarningRule"/>
                        <error line="3" column="33" severity="error" message="Error" source="ErrorRule"/>
                        <error severity="fatal" message="Fatal"/>
                      </file>
                      <file name="%1\$s/Fixtures/file2.twig">
                        <error line="1" column="11" severity="notice" message="Notice2" source="Notice2Rule"/>
                      </file>
                      <file name="%1\$s/Fixtures/file3.twig">
                        <error severity="fatal" message="&apos;&quot;&lt;&amp;&gt;&quot;&apos;"/>
                      </file>
                    </checkstyle>
                    EOD,
                __DIR__
            ),
            null,
        ];

        yield [
            sprintf(
                <<<EOD
                    <?xml version="1.0" encoding="UTF-8"?>
                    <checkstyle>
                      <file name="%1\$s/Fixtures/file.twig">
                        <error line="3" column="33" severity="error" message="Error" source="ErrorRule"/>
                        <error severity="fatal" message="Fatal"/>
                      </file>
                      <file name="%1\$s/Fixtures/file3.twig">
                        <error severity="fatal" message="&apos;&quot;&lt;&amp;&gt;&quot;&apos;"/>
                      </file>
                    </checkstyle>
                    EOD,
                __DIR__
            ),
            Report::MESSAGE_TYPE_ERROR,
        ];
    }
}
