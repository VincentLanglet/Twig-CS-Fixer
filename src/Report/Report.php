<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function in_array;
use function sprintf;

/**
 * Report contains all violations with stats.
 */
final class Report
{
    public const MESSAGE_TYPE_NOTICE  = 'NOTICE';
    public const MESSAGE_TYPE_WARNING = 'WARNING';
    public const MESSAGE_TYPE_ERROR   = 'ERROR';
    public const MESSAGE_TYPE_FATAL   = 'FATAL';

    /**
     * @var array<string, list<SniffViolation>>
     */
    private array $messagesByFiles = [];

    /**
     * @var list<string>
     */
    private array $files = [];

    /**
     * @var int
     */
    private int $totalNotices = 0;

    /**
     * @var int
     */
    private int $totalWarnings = 0;

    /**
     * @var int
     */
    private int $totalErrors = 0;

    /**
     * @param SniffViolation $sniffViolation
     *
     * @return self
     */
    public function addMessage(SniffViolation $sniffViolation): Report
    {
        $filename = $sniffViolation->getFilename();
        if (!in_array($filename, $this->getFiles(), true)) {
             throw new \InvalidArgumentException(
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
     * @param string|null $level
     *
     * @return array<string, list<SniffViolation>>
     */
    public function getMessagesByFiles(?string $level = null): array
    {
        if (null === $level) {
            return $this->messagesByFiles;
        }

        return array_map(static function (array $messages) use ($level): array {
            return array_values(
                array_filter($messages, static function (SniffViolation $message) use ($level): bool {
                    return $message->getLevel() >= SniffViolation::getLevelAsInt($level);
                })
            );
        }, $this->messagesByFiles);
    }

    /**
     * @param string $file
     *
     * @return void
     */
    public function addFile(string $file): void
    {
        $this->files[] = $file;
        $this->messagesByFiles[$file] = [];
    }

    /**
     * @return list<string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return int
     */
    public function getTotalFiles(): int
    {
        return count($this->files);
    }

    /**
     * @return int
     */
    public function getTotalNotices(): int
    {
        return $this->totalNotices;
    }

    /**
     * @return int
     */
    public function getTotalWarnings(): int
    {
        return $this->totalWarnings;
    }

    /**
     * @return int
     */
    public function getTotalErrors(): int
    {
        return $this->totalErrors;
    }
}
