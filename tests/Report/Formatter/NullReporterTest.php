<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report\Formatter;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\NullReporter;
use TwigCsFixer\Report\SniffViolation;

final class NullReporterTest extends TestCase
{
    /**
     * @dataProvider displayDataProvider
     */
    public function testDisplayErrors(?string $level): void
    {
        $textFormatter = new NullReporter();

        $file = __DIR__.'/Fixtures/file.twig';
        $report = new Report([new SplFileInfo($file)]);

        $violation0 = new SniffViolation(SniffViolation::LEVEL_NOTICE, 'Notice', $file, 1);
        $report->addMessage($violation0);
        $violation1 = new SniffViolation(SniffViolation::LEVEL_WARNING, 'Warning', $file, 2);
        $report->addMessage($violation1);
        $violation2 = new SniffViolation(SniffViolation::LEVEL_ERROR, 'Error', $file, 3);
        $report->addMessage($violation2);
        $violation3 = new SniffViolation(SniffViolation::LEVEL_FATAL, 'Fatal', $file);
        $report->addMessage($violation3);

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $textFormatter->display($output, $report, $level);

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
