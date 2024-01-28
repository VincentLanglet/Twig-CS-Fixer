<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use InvalidArgumentException;

/**
 * Wrapper class that represents a violation to a rule with context.
 */
final class Violation
{
    public const LEVEL_NOTICE = 0;
    public const LEVEL_WARNING = 1;
    public const LEVEL_ERROR = 2;
    public const LEVEL_FATAL = 3;

    public function __construct(
        private int $level,
        private string $message,
        private string $filename,
        private ?string $ruleName = null,
        private ?ViolationId $identifier = null,
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
            default             => throw new InvalidArgumentException(
                sprintf('Level "%s" is not supported.', $level)
            ),
        };
    }

    public static function getLevelAsInt(string $level): int
    {
        return match (strtoupper($level)) {
            Report::MESSAGE_TYPE_NOTICE  => self::LEVEL_NOTICE,
            Report::MESSAGE_TYPE_WARNING => self::LEVEL_WARNING,
            Report::MESSAGE_TYPE_ERROR   => self::LEVEL_ERROR,
            Report::MESSAGE_TYPE_FATAL   => self::LEVEL_FATAL,
            default                      => throw new InvalidArgumentException(
                sprintf('Level "%s" is not supported.', $level)
            ),
        };
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDebugMessage(bool $debug = true): string
    {
        if (!$debug) {
            return $this->message;
        }

        return $this->identifier?->toString() ?? $this->message;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getRuleName(): ?string
    {
        return $this->ruleName;
    }

    public function getIdentifier(): ?ViolationId
    {
        return $this->identifier;
    }

    public function getLine(): ?int
    {
        return $this->identifier?->getLine();
    }

    public function getLinePosition(): ?int
    {
        return $this->identifier?->getLinePosition();
    }
}
