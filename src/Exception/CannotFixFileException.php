<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

use RuntimeException;
use Throwable;

final class CannotFixFileException extends RuntimeException
{
    private function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fileNotReadable(string $path): self
    {
        return new self(sprintf('Cannot read the content of the file "%s".', $path));
    }

    public static function infiniteLoop(string $path): self
    {
        return new self(sprintf('Too many iteration while trying to fix file "%s".', $path));
    }
}
