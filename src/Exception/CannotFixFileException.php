<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

final class CannotFixFileException extends \RuntimeException
{
    private function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function infiniteLoop(): self
    {
        return new self('Too many iteration while trying to fix file.');
    }
}
