<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\CheckstyleReporter;
use TwigCsFixer\Report\Reporter\GithubReporter;
use TwigCsFixer\Report\Reporter\JUnitReporter;
use TwigCsFixer\Report\Reporter\NullReporter;
use TwigCsFixer\Report\Reporter\ReporterInterface;
use TwigCsFixer\Report\Reporter\TextReporter;
use TwigCsFixer\Report\ReporterFactory;

final class ReporterFactoryTest extends TestCase
{
    public function testGetReporter(): void
    {
        $reporterFactory = new ReporterFactory();

        static::assertInstanceOf(TextReporter::class, $reporterFactory->getReporter());
        static::assertInstanceOf(TextReporter::class, $reporterFactory->getReporter(TextReporter::NAME));
        static::assertInstanceOf(NullReporter::class, $reporterFactory->getReporter(NullReporter::NAME));
        static::assertInstanceOf(CheckstyleReporter::class, $reporterFactory->getReporter(CheckstyleReporter::NAME));
        static::assertInstanceOf(JUnitReporter::class, $reporterFactory->getReporter(JUnitReporter::NAME));
        static::assertInstanceOf(GithubReporter::class, $reporterFactory->getReporter(GithubReporter::NAME));
    }

    public function testGetUnsupporterReporter(): void
    {
        $reporterFactory = new ReporterFactory();

        $this->expectExceptionMessage('No reporter supports the format "foo".');
        $reporterFactory->getReporter('foo');
    }

    public function testGetCustomReporter(): void
    {
        $fooReporter = new class implements ReporterInterface {
            public function getName(): string
            {
                return 'foo';
            }

            public function display(
                OutputInterface $output,
                Report $report,
                ?string $level,
                bool $debug
            ): void {
            }
        };

        $reporterFactory = new ReporterFactory([$fooReporter]);

        static::assertSame($fooReporter, $reporterFactory->getReporter('foo'));
    }
}
