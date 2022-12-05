<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use InvalidArgumentException;
use SplFileInfo;

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
     * @var array<string, list<SniffViolation>>
     */
    private array $messagesByFiles = [];

    private int $totalNotices = 0;

    private int $totalWarnings = 0;

    private int $totalErrors = 0;

    /**
     * @param iterable<SplFileInfo> $files
     */
    public function __construct(iterable $files)
    {
        foreach ($files as $file) {
            $this->messagesByFiles[$file->getPathname()] = [];
        }
    }

    public function addMessage(SniffViolation $sniffViolation): self
    {
        $filename = $sniffViolation->getFilename();
        if (!isset($this->messagesByFiles[$filename])) {
            throw new InvalidArgumentException(
                sprintf('The file "%s" is not handled by this report.', $filename)
            );
        }

        // Update stats
        switch ($sniffViolation->getLevel()) {
            case SniffViolation::LEVEL_NOTICE:
                $this->totalNotices++;
                break;
            case SniffViolation::LEVEL_WARNING:
                $this->totalWarnings++;
                break;
            case SniffViolation::LEVEL_ERROR:
            case SniffViolation::LEVEL_FATAL:
                $this->totalErrors++;
                break;
        }

        $this->messagesByFiles[$filename][] = $sniffViolation;

        return $this;
    }

    /**
     * @return list<SniffViolation>
     */
    public function getMessages(string $filename, ?string $level = null): array
    {
        if (!isset($this->messagesByFiles[$filename])) {
            throw new InvalidArgumentException(
                sprintf('The file "%s" is not handled by this report.', $filename)
            );
        }

        if (null === $level) {
            return $this->messagesByFiles[$filename];
        }

        return array_values(
            array_filter(
                $this->messagesByFiles[$filename],
                static fn (SniffViolation $message): bool => $message->getLevel() >= SniffViolation::getLevelAsInt($level)
            )
        );
    }

    /**
     * @return list<string>
     */
    public function getFiles(): array
    {
        return array_keys($this->messagesByFiles);
    }

    public function getTotalFiles(): int
    {
        return \count($this->messagesByFiles);
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
