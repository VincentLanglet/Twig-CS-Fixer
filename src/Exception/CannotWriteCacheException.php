<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

use RuntimeException;
use Throwable;

final class CannotWriteCacheException extends RuntimeException
{
    private function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function because(Throwable $throwable): self
    {
        return new self($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
    }

    public static function locationIsDirectory(string $path): self
    {
        return new self(sprintf('Cannot write cache file "%s" as the location exists as directory.', $path));
    }

    public static function locationIsNotWritable(string $path): self
    {
        return new self(sprintf('Cannot write to file "%s" as it is not writable.', $path));
    }

    public static function missingDirectory(string $path): self
    {
        return new self(sprintf('Directory of cache file "%s" does not exists.', $path));
    }
}
