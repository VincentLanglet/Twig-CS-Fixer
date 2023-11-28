<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use InvalidArgumentException;
use TwigCsFixer\Report\Reporter\CheckstyleReporter;
use TwigCsFixer\Report\Reporter\GithubReporter;
use TwigCsFixer\Report\Reporter\JUnitReporter;
use TwigCsFixer\Report\Reporter\NullReporter;
use TwigCsFixer\Report\Reporter\ReporterInterface;
use TwigCsFixer\Report\Reporter\TextReporter;

final class ReporterFactory
{
    public function getReporter(string $format = TextReporter::NAME): ReporterInterface
    {
        return match ($format) {
            NullReporter::NAME       => new NullReporter(),
            TextReporter::NAME       => new TextReporter(),
            CheckstyleReporter::NAME => new CheckstyleReporter(),
            JUnitReporter::NAME      => new JUnitReporter(),
            GithubReporter::NAME     => new GithubReporter(),
            default                  => throw new InvalidArgumentException(
                sprintf('No reporter supports the format "%s".', $format)
            ),
        };
    }
}
