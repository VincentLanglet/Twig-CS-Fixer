<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

use LogicException;
use TwigCsFixer\Sniff\SniffInterface;

/**
 * Wrapper class that represents a violation to a sniff with context.
 */
class SniffViolation
{
    private const LEVEL_NOTICE  = 'NOTICE';
    private const LEVEL_WARNING = 'WARNING';
    private const LEVEL_ERROR   = 'ERROR';
    private const LEVEL_FATAL   = 'FATAL';

    /**
     * @var int
     */
    protected $level;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int|null
     */
    protected $line;

    /**
     * @var int|null
     */
    protected $linePosition;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var SniffInterface|null
     */
    protected $sniff;

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
            case Report::MESSAGE_TYPE_NOTICE:
                return self::LEVEL_NOTICE;
            case Report::MESSAGE_TYPE_WARNING:
                return self::LEVEL_WARNING;
            case Report::MESSAGE_TYPE_ERROR:
                return self::LEVEL_ERROR;
            case Report::MESSAGE_TYPE_FATAL:
                return self::LEVEL_FATAL;
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
        switch (mb_strtoupper($level)) {
            case self::LEVEL_NOTICE:
                return Report::MESSAGE_TYPE_NOTICE;
            case self::LEVEL_WARNING:
                return Report::MESSAGE_TYPE_WARNING;
            case self::LEVEL_ERROR:
                return Report::MESSAGE_TYPE_ERROR;
            case self::LEVEL_FATAL:
                return Report::MESSAGE_TYPE_FATAL;
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

    /**
     * @param SniffInterface $sniff
     *
     * @return self
     */
    public function setSniff(SniffInterface $sniff): SniffViolation
    {
        $this->sniff = $sniff;

        return $this;
    }

    /**
     * @return SniffInterface|null
     */
    public function getSniff(): ?SniffInterface
    {
        return $this->sniff;
    }
}
