<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

final class CannotTokenizeException extends \RuntimeException
{
    private function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function unclosedBracket(string $character, int $line): self
    {
        return new self(\sprintf('Unclosed "%s" at line %s.', $character, $line));
    }

    public static function unclosedComment(int $line): self
    {
        return new self(\sprintf('Unclosed comment at line %s.', $line));
    }

    public static function unexpectedCharacter(string $character, int $line): self
    {
        return new self(\sprintf('Unexpected character "%s" at line %s.', $character, $line));
    }

    public static function unknownError(): self
    {
        return new self('The template is invalid.');
    }
}
