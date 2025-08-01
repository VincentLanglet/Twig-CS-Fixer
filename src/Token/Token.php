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
    public const SPREAD_TYPE = 13;
    // New constants
    public const DQ_STRING_START_TYPE = 'DQ_STRING_START_TYPE';
    public const DQ_STRING_END_TYPE = 'DQ_STRING_END_TYPE';
    public const BLOCK_NAME_TYPE = 'BLOCK_NAME_TYPE';
    public const FUNCTION_NAME_TYPE = 'FUNCTION_NAME_TYPE';
    public const FILTER_NAME_TYPE = 'FILTER_NAME_TYPE';
    public const MACRO_NAME_TYPE = 'MACRO_NAME_TYPE';
    public const MACRO_VAR_NAME_TYPE = 'MACRO_VAR_NAME_TYPE';
    public const TEST_NAME_TYPE = 'TEST_NAME_TYPE';
    public const HASH_KEY_NAME_TYPE = 'HASH_KEY_NAME_TYPE';
    public const TYPE_NAME_TYPE = 'TYPE_NAME_TYPE';
    public const WHITESPACE_TYPE = 'WHITESPACE_TYPE';
    public const TAB_TYPE = 'TAB_TYPE';
    public const EOL_TYPE = 'EOL_TYPE';
    public const COMMENT_START_TYPE = 'COMMENT_START_TYPE';
    public const COMMENT_TEXT_TYPE = 'COMMENT_TEXT_TYPE';
    public const COMMENT_WHITESPACE_TYPE = 'COMMENT_WHITESPACE_TYPE';
    public const COMMENT_TAB_TYPE = 'COMMENT_TAB_TYPE';
    public const COMMENT_EOL_TYPE = 'COMMENT_EOL_TYPE';
    public const COMMENT_END_TYPE = 'COMMENT_END_TYPE';
    public const INLINE_COMMENT_START_TYPE = 'INLINE_COMMENT_START_TYPE';
    public const INLINE_COMMENT_TEXT_TYPE = 'INLINE_COMMENT_TEXT_TYPE';
    public const INLINE_COMMENT_WHITESPACE_TYPE = 'INLINE_COMMENT_WHITESPACE_TYPE';
    public const INLINE_COMMENT_TAB_TYPE = 'INLINE_COMMENT_TAB_TYPE';
    public const NAMED_ARGUMENT_SEPARATOR_TYPE = 'NAMED_ARGUMENT_SEPARATOR_TYPE';

    public const WHITESPACE_TOKENS = [
        self::WHITESPACE_TYPE => self::WHITESPACE_TYPE,
        self::COMMENT_WHITESPACE_TYPE => self::COMMENT_WHITESPACE_TYPE,
        self::INLINE_COMMENT_WHITESPACE_TYPE => self::INLINE_COMMENT_WHITESPACE_TYPE,
    ];

    public const TAB_TOKENS = [
        self::TAB_TYPE => self::TAB_TYPE,
        self::COMMENT_TAB_TYPE => self::COMMENT_TAB_TYPE,
        self::INLINE_COMMENT_TAB_TYPE => self::INLINE_COMMENT_TAB_TYPE,
    ];

    public const INDENT_TOKENS = self::WHITESPACE_TOKENS + self::TAB_TOKENS;

    public const EOL_TOKENS = [
        self::EOL_TYPE => self::EOL_TYPE,
        self::COMMENT_EOL_TYPE => self::COMMENT_EOL_TYPE,
    ];

    public const COMMENT_TOKENS = [
        self::COMMENT_START_TYPE => self::COMMENT_START_TYPE,
        self::COMMENT_TEXT_TYPE => self::COMMENT_TEXT_TYPE,
        self::COMMENT_WHITESPACE_TYPE => self::COMMENT_WHITESPACE_TYPE,
        self::COMMENT_TAB_TYPE => self::COMMENT_TAB_TYPE,
        self::COMMENT_EOL_TYPE => self::COMMENT_EOL_TYPE,
        self::COMMENT_END_TYPE => self::COMMENT_END_TYPE,
        self::INLINE_COMMENT_START_TYPE => self::INLINE_COMMENT_START_TYPE,
        self::INLINE_COMMENT_TEXT_TYPE => self::INLINE_COMMENT_TEXT_TYPE,
        self::INLINE_COMMENT_WHITESPACE_TYPE => self::INLINE_COMMENT_WHITESPACE_TYPE,
        self::INLINE_COMMENT_TAB_TYPE => self::INLINE_COMMENT_TAB_TYPE,
    ];

    public const EMPTY_TOKENS = self::INDENT_TOKENS + self::EOL_TOKENS + self::COMMENT_TOKENS;

    public const BLOCK_TOKENS = [
        self::BLOCK_START_TYPE => self::BLOCK_START_TYPE,
        self::BLOCK_NAME_TYPE => self::BLOCK_NAME_TYPE,
        self::BLOCK_END_TYPE => self::BLOCK_END_TYPE,
    ];

    public function __construct(
        private int|string $type,
        private int $line,
        private int $linePosition,
        private string $filename,
        private string $value = '',
        private ?self $relatedToken = null,
    ) {
    }

    public function getType(): int|string
    {
        return $this->type;
    }

    public function setType(int|string $type): void
    {
        $this->type = $type;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getLinePosition(): int
    {
        return $this->linePosition;
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

    public function setRelatedToken(self $token): void
    {
        $this->relatedToken = $token;
    }

    /**
     * @param int|string|array<int|string> $type
     * @param string|string[]              $value
     */
    public function isMatching(int|string|array $type, string|array $value = []): bool
    {
        if (!\is_array($type)) {
            $type = [$type];
        }
        if (!\is_array($value)) {
            $value = [$value];
        }

        return \in_array($this->getType(), $type, true)
            && ([] === $value || \in_array($this->getValue(), $value, true));
    }
}
