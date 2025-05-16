<?php

declare(strict_types=1);

namespace TwigCsFixer\Report\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\File\FileHelper;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;

/**
 * Allow errors to be reported in pull-requests diff when run in a Gitlab Merge Request.
 *
 * @see https://docs.gitlab.com/ci/testing/code_quality/#code-quality-report-format
 */
final class GitlabReporter implements ReporterInterface
{
    public const NAME = 'gitlab';

    /** @var array<string> */
    private array $hashes = [];

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @throws \JsonException
     */
    public function display(
        OutputInterface $output,
        Report $report,
        ?string $level,
        bool $debug,
    ): void {
        $reports = [];

        foreach ($report->getViolations() as $violation) {
            $filename = $violation->getFilename();
            $severity = match ($violation->getLevel()) {
                Violation::LEVEL_WARNING => 'minor',
                Violation::LEVEL_ERROR => 'major',
                Violation::LEVEL_FATAL => 'critical',
                default => 'info',
            };

            $reports[] = [
                'description' => $violation->getDebugMessage($debug),
                'check_name' => $violation->getRuleName() ?? '',
                'fingerprint' => $this->generateFingerprint($filename, $violation),
                'severity' => $severity,
                'location' => [
                    'path' => $filename,
                    'lines' => [
                        'begin' => $violation->getLine() ?? 1,
                    ],
                ],
            ];
        }

        $json = json_encode($reports, \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR);

        $output->writeln($json);
    }

    /**
     * Generate a unique fingerprint to identify this specific code quality violation, such as a hash of its contents.
     *
     * We do not use the ViolationId to generate the fingerprint because :
     * - The ViolationId::toString returns the line and linePosition of the violation.
     * - Using code location when creating hash for Gitlab fingerprints makes the code-quality reports in Gitlab very unstable.
     * - Any change of position would trigger both a "fixed" message, and a "new problem detected" message in Gitlab, making it very noisy.
     *
     * @see https://github.com/astral-sh/ruff/pull/7203
     */
    private function generateFingerprint(string $relativePath, Violation $violation): string
    {
        // Use the same separator cross-platform to generate the same fingerprint.
        $normalizedPath = FileHelper::normalizePath($relativePath, '/');
        $base = $normalizedPath.$violation->getRuleName().$violation->getMessage();

        $hash = md5($base);

        // Check if the generated hash does not already exist
        // Keep generating new hashes until we get a unique one
        while (\in_array($hash, $this->hashes, true)) {
            $hash = md5($hash);
        }

        $this->hashes[] = $hash;

        return $hash;
    }
}
