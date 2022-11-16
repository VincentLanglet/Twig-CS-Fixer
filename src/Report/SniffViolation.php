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

    private ?int $linePosition = null;

    public function __construct(
        private int $level,
        private string $message,
        private string $filename,
        private ?int $line = null
    ) {
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public static function getLevelAsString(int $level): string
    {
        return match ($level) {
            self::LEVEL_NOTICE  => Report::MESSAGE_TYPE_NOTICE,
            self::LEVEL_WARNING => Report::MESSAGE_TYPE_WARNING,
            self::LEVEL_ERROR   => Report::MESSAGE_TYPE_ERROR,
            self::LEVEL_FATAL   => Report::MESSAGE_TYPE_FATAL,
            default             => throw new InvalidArgumentException(sprintf('Level "%s" is not supported.', $level)),
        };
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
