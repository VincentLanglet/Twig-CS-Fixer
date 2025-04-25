<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use TwigCsFixer\Report\Reporter\CheckstyleReporter;
use TwigCsFixer\Report\Reporter\GithubReporter;
use TwigCsFixer\Report\Reporter\GitlabReporter;
use TwigCsFixer\Report\Reporter\JUnitReporter;
use TwigCsFixer\Report\Reporter\NullReporter;
use TwigCsFixer\Report\Reporter\ReporterInterface;
use TwigCsFixer\Report\Reporter\TextReporter;

final class ReporterFactory
{
    /**
     * @param list<ReporterInterface> $customReporters
     */
    public function __construct(
        private array $customReporters = [],
    ) {
    }

    public function getReporter(string $format = TextReporter::NAME): ReporterInterface
    {
        foreach ($this->customReporters as $reporter) {
            if ($format === $reporter->getName()) {
                return $reporter;
            }
        }

        return match ($format) {
            NullReporter::NAME => new NullReporter(),
            TextReporter::NAME => new TextReporter(),
            CheckstyleReporter::NAME => new CheckstyleReporter(),
            JUnitReporter::NAME => new JUnitReporter(),
            GithubReporter::NAME => new GithubReporter(),
            GitlabReporter::NAME => new GitlabReporter(),
            default => throw new \InvalidArgumentException(
                \sprintf('No reporter supports the format "%s".', $format)
            ),
        };
    }
}
