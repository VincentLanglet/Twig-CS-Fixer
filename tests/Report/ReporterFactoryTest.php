<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\Reporter\CheckstyleReporter;
use TwigCsFixer\Report\Reporter\NullReporter;
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
    }

    public function testGetMessageForAnotherFile(): void
    {
        $reporterFactory = new ReporterFactory();

        $this->expectExceptionMessage('No reporter supports the format "foo".');
        $reporterFactory->getReporter('foo');
    }
}
