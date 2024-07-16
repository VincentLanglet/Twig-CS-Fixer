<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Reporter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\NullReporter;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Tests\TestHelper;

final class NullReporterTest extends TestCase
{

    public function testGetName(): void
    {
        static::assertSame(NullReporter::NAME, (new NullReporter())->getName());
    }

    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(?string $level): void
    {
        $textFormatter = new NullReporter();

        $file = TestHelper::getOsPath(__DIR__.'/Fixtures/file.twig');
        $report = new Report([new \SplFileInfo($file)]);

        $violation0 = new Violation(Violation::LEVEL_NOTICE, 'Notice', $file);
        $report->addViolation($violation0);
        $violation1 = new Violation(Violation::LEVEL_WARNING, 'Warning', $file);
        $report->addViolation($violation1);
        $violation2 = new Violation(Violation::LEVEL_ERROR, 'Error', $file);
        $report->addViolation($violation2);
        $violation3 = new Violation(Violation::LEVEL_FATAL, 'Fatal', $file);
        $report->addViolation($violation3);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, $level, false);

        $text = $output->fetch();
        static::assertSame('', $text);
    }

    /**
     * @return iterable<array-key, array{string|null}>
     */
    public static function displayDataProvider(): iterable
    {
        yield [null];
        yield [Report::MESSAGE_TYPE_NOTICE];
        yield [Report::MESSAGE_TYPE_WARNING];
        yield [Report::MESSAGE_TYPE_ERROR];
        yield [Report::MESSAGE_TYPE_FATAL];
    }
}
