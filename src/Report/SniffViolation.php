<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use LogicException;

use function sprintf;
use function strtoupper;

/**
 * Wrapper class that represents a violation to a sniff with context.
 */
final class SniffViolation
{
    public const LEVEL_NOTICE  = 0;
    public const LEVEL_WARNING = 1;
    public const LEVEL_ERROR   = 2;
    public const LEVEL_FATAL   = 3;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int|null
     */
    private $line;

    /**
     * @var int|null
     */
    private $linePosition;

    /**
     * @var string
     */
    private $filename;

    /**
     * @param int      $level
     * @param string   $message
     * @param string   $filename
     * @param int|null $line
     *
     * @return void
     */
    public function __construct(int $level, string $message, string $filename, int $line = null)
    {
        $this->level = $level;
        $this->message = $message;
        $this->line = $line;
        $this->filename = $filename;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLevelAsString(): string
    {
        switch ($this->level) {
            case self::LEVEL_NOTICE:
                return Report::MESSAGE_TYPE_NOTICE;
            case self::LEVEL_WARNING:
                return Report::MESSAGE_TYPE_WARNING;
            case self::LEVEL_ERROR:
                return Report::MESSAGE_TYPE_ERROR;
            case self::LEVEL_FATAL:
                return Report::MESSAGE_TYPE_FATAL;
            default:
                throw new LogicException(sprintf('Level "%s" is not supported.', $this->level));
        }
    }

    /**
     * @param string $level
     *
     * @return int
     */
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
                throw new LogicException(sprintf('Level "%s" is not supported.', $level));
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int|null
     */
    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param int|null $linePosition
     *
     * @return self
     */
    public function setLinePosition(?int $linePosition): SniffViolation
    {
        $this->linePosition = $linePosition;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLinePosition(): ?int
    {
        return $this->linePosition;
    }
}
