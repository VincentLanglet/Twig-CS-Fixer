<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use InvalidArgumentException;
use TwigCsFixer\Report\Reporter\NullReporter;
use TwigCsFixer\Report\Reporter\ReporterInterface;
use TwigCsFixer\Report\Reporter\TextReporter;
use TwigCsFixer\Report\Reporter\CheckstyleReporter;

final class ReporterFactory
{
    public function getReporter(string $format = TextReporter::NAME): ReporterInterface
    {
        return match ($format) {
            NullReporter::NAME => new NullReporter(),
            TextReporter::NAME => new TextReporter(),
            CheckstyleReporter::NAME => new CheckstyleReporter(),
            default            => throw new InvalidArgumentException(
                sprintf('No reporter supports the format "%s".', $format)
            ),
        };
    }
}
