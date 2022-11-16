<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

/**
 * Override of Twig's Token to add new constants.
 */
final class Token
{
    // From Twig\Token
    public const EOF_TYPE = -1;
    public const TEXT_TYPE = 0;
    public const BLOCK_START_TYPE = 1;
    public const VAR_START_TYPE = 2;
    public const BLOCK_END_TYPE = 3;
    public const VAR_END_TYPE = 4;
    public const NAME_TYPE = 5;
    public const NUMBER_TYPE = 6;
    public const STRING_TYPE = 7;
    public const OPERATOR_TYPE = 8;
    public const PUNCTUATION_TYPE = 9;
    public const INTERPOLATION_START_TYPE = 10;
    public const INTERPOLATION_END_TYPE = 11;
    public const ARROW_TYPE = 12;
    // New constants
    public const DQ_STRING_START_TYPE = 13;
    public const DQ_STRING_END_TYPE = 14;
    public const BLOCK_TAG_TYPE = 15;
    public const WHITESPACE_TYPE = 16;
    public const TAB_TYPE = 17;
    public const EOL_TYPE = 18;
    public const COMMENT_START_TYPE = 19;
    public const COMMENT_TEXT_TYPE = 20;
    public const COMMENT_WHITESPACE_TYPE = 21;
    public const COMMENT_TAB_TYPE = 22;
    public const COMMENT_EOL_TYPE = 23;
    public const COMMENT_END_TYPE = 24;

    public const WHITESPACE_TOKENS = [
        self::WHITESPACE_TYPE         => self::WHITESPACE_TYPE,
        self::COMMENT_WHITESPACE_TYPE => self::COMMENT_WHITESPACE_TYPE,
    ];

    public const TAB_TOKENS = [
        self::TAB_TYPE         => self::TAB_TYPE,
        self::COMMENT_TAB_TYPE => self::COMMENT_TAB_TYPE,
    ];

    public const INDENT_TOKENS = self::WHITESPACE_TOKENS + self::TAB_TOKENS;

    public const EOL_TOKENS = [
        self::EOL_TYPE         => self::EOL_TYPE,
        self::COMMENT_EOL_TYPE => self::COMMENT_EOL_TYPE,
    ];

    public const EMPTY_TOKENS = self::INDENT_TOKENS + self::EOL_TOKENS + [
        self::COMMENT_START_TYPE => self::COMMENT_START_TYPE,
        self::COMMENT_TEXT_TYPE  => self::COMMENT_TEXT_TYPE,
        self::COMMENT_END_TYPE   => self::COMMENT_END_TYPE,
    ];

    public function __construct(
        private int $type,
        private int $line,
        private int $position,
        private string $filename,
        private string $value = '',
        private ?self $relatedToken = null
    ) {
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getRelatedToken(): ?self
    {
        return $this->relatedToken;
    }
}
