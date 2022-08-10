<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use InvalidArgumentException;

/**
 * Wrapper class that represents a violation to a sniff with context.
 */
final class SniffViolation
{
    public const LEVEL_NOTICE = 0;
    public const LEVEL_WARNING = 1;
    public const LEVEL_ERROR = 2;
    public const LEVEL_FATAL = 3;

    private int $level;

    private string $message;

    private ?int $line;

    private ?int $linePosition = null;

    private string $filename;

    public function __construct(int $level, string $message, string $filename, ?int $line = null)
    {
        $this->level = $level;
        $this->message = $message;
        $this->line = $line;
        $this->filename = $filename;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public static function getLevelAsString(int $level): string
    {
        switch ($level) {
            case self::LEVEL_NOTICE:
                return Report::MESSAGE_TYPE_NOTICE;
            case self::LEVEL_WARNING:
                return Report::MESSAGE_TYPE_WARNING;
            case self::LEVEL_ERROR:
                return Report::MESSAGE_TYPE_ERROR;
            case self::LEVEL_FATAL:
                return Report::MESSAGE_TYPE_FATAL;
            default:
                throw new InvalidArgumentException(sprintf('Level "%s" is not supported.', $level));
        }
    }

    public static function getLevelAsInt(string $level): int
    {
        switch (strtoupper($level)) {
            case Report::MESSAGE_TYPE_NOTICE:
                return self::LEVEL_NOTICE;
            case Report::MESSAGE_TYPE_WARNING:
                return self::LEVEL_WARNING;
            case Report::MESSAGE_TYPE_ERROR:
                return self::LEVEL_ERROR;
            case Report::MESSAGE_TYPE_FATAL:
                return self::LEVEL_FATAL;
            default:
                throw new InvalidArgumentException(sprintf('Level "%s" is not supported.', $level));
        }
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setLinePosition(?int $linePosition): self
    {
        $this->linePosition = $linePosition;

        return $this;
    }

    public function getLinePosition(): ?int
    {
        return $this->linePosition;
    }
}
