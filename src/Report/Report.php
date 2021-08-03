<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

/**
 * Report contains all violations with stats.
 */
final class Report
{
    public const MESSAGE_TYPE_NOTICE  = 0;
    public const MESSAGE_TYPE_WARNING = 1;
    public const MESSAGE_TYPE_ERROR   = 2;
    public const MESSAGE_TYPE_FATAL   = 3;

    /**
     * @var SniffViolation[]
     */
    private $messages = [];

    /**
     * @var string[]
     */
    private $files = [];

    /**
     * @var int
     */
    private $totalNotices = 0;

    /**
     * @var int
     */
    private $totalWarnings = 0;

    /**
     * @var int
     */
    private $totalErrors = 0;

    /**
     * @param SniffViolation $sniffViolation
     *
     * @return self
     */
    public function addMessage(SniffViolation $sniffViolation): Report
    {
        // Update stats
        switch ($sniffViolation->getLevel()) {
            case self::MESSAGE_TYPE_NOTICE:
                ++$this->totalNotices;
                break;
            case self::MESSAGE_TYPE_WARNING:
                ++$this->totalWarnings;
                break;
            case self::MESSAGE_TYPE_ERROR:
            case self::MESSAGE_TYPE_FATAL:
                ++$this->totalErrors;
                break;
        }

        $this->messages[] = $sniffViolation;

        return $this;
    }

    /**
     * @param array{file?: string, level?: string|null} $filters
     *
     * @return SniffViolation[]
     */
    public function getMessages(array $filters = []): array
    {
        if (0 === count($filters)) {
            // Return all messages, without filtering.
            return $this->messages;
        }

        return array_filter($this->messages, static function (SniffViolation $message) use ($filters): bool {
            $fileFilter = true;
            $levelFilter = true;

            if (isset($filters['file'])) {
                $fileFilter = $message->getFilename() === $filters['file'];
            }

            if (isset($filters['level'])) {
                $levelFilter = $message->getLevel() >= $message::getLevelAsInt($filters['level']);
            }

            return $fileFilter && $levelFilter;
        });
    }

    /**
     * @param string $file
     *
     * @return void
     */
    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }

    /**
     * @return string[]
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
    public function getTotalMessages(): int
    {
        return count($this->messages);
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
