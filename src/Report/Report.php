<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

/**
 * Report contains all violations with stats.
 */
final class Report
{
    public const MESSAGE_TYPE_NOTICE = 'NOTICE';
    public const MESSAGE_TYPE_WARNING = 'WARNING';
    public const MESSAGE_TYPE_ERROR = 'ERROR';
    public const MESSAGE_TYPE_FATAL = 'FATAL';

    /**
     * @var array<string, list<Violation>>
     */
    private array $violationsByFile = [];

    /**
     * @var array<string, true>
     */
    private array $fixedFiles = [];

    private int $totalNotices = 0;

    private int $totalWarnings = 0;

    private int $totalErrors = 0;

    /**
     * @var array<string, string>
     */
    private array $realPaths = [];

    /**
     * @param iterable<\SplFileInfo> $files
     */
    public function __construct(iterable $files)
    {
        foreach ($files as $file) {
            $pathName = $file->getPathname();
            $realPath = $file->getRealPath();

            $this->realPaths[$pathName] = false !== $realPath ? $realPath : $pathName;
            $this->violationsByFile[$pathName] = [];
        }
    }

    public function addViolation(Violation $violation): self
    {
        $filename = $violation->getFilename();
        if (!isset($this->violationsByFile[$filename])) {
            throw new \InvalidArgumentException(
                \sprintf('The file "%s" is not handled by this report.', $filename)
            );
        }

        // Update stats
        switch ($violation->getLevel()) {
            case Violation::LEVEL_NOTICE:
                $this->totalNotices++;
                break;
            case Violation::LEVEL_WARNING:
                $this->totalWarnings++;
                break;
            case Violation::LEVEL_ERROR:
            case Violation::LEVEL_FATAL:
                $this->totalErrors++;
                break;
        }

        $this->violationsByFile[$filename][] = $violation;

        return $this;
    }

    /**
     * @return list<Violation>
     */
    public function getFileViolations(string $filename, ?string $level = null): array
    {
        if (!isset($this->violationsByFile[$filename])) {
            throw new \InvalidArgumentException(
                \sprintf('The file "%s" is not handled by this report.', $filename)
            );
        }

        if (null === $level) {
            return $this->violationsByFile[$filename];
        }

        return array_values(
            array_filter(
                $this->violationsByFile[$filename],
                static fn (Violation $message): bool => $message->getLevel() >= Violation::getLevelAsInt($level)
            )
        );
    }

    /**
     * @return list<Violation>
     */
    public function getViolations(?string $level = null): array
    {
        $messages = array_merge(...array_values($this->violationsByFile));

        if (null === $level) {
            return $messages;
        }

        return array_values(
            array_filter(
                $messages,
                static fn (Violation $message): bool => $message->getLevel() >= Violation::getLevelAsInt($level)
            )
        );
    }

    /**
     * @return list<string>
     */
    public function getFiles(): array
    {
        return array_keys($this->violationsByFile);
    }

    public function getRealPath(string $filename): string
    {
        if (!isset($this->realPaths[$filename])) {
            throw new \InvalidArgumentException(
                \sprintf('The file "%s" is not handled by this report.', $filename)
            );
        }

        return $this->realPaths[$filename];
    }

    public function getTotalFiles(): int
    {
        return \count($this->violationsByFile);
    }

    public function addFixedFile(string $filename): self
    {
        if (!isset($this->violationsByFile[$filename])) {
            throw new \InvalidArgumentException(
                \sprintf('The file "%s" is not handled by this report.', $filename)
            );
        }

        $this->fixedFiles[$filename] = true;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getFixedFiles(): array
    {
        return array_keys($this->fixedFiles);
    }

    public function getTotalNotices(): int
    {
        return $this->totalNotices;
    }

    public function getTotalWarnings(): int
    {
        return $this->totalWarnings;
    }

    public function getTotalErrors(): int
    {
        return $this->totalErrors;
    }
}
