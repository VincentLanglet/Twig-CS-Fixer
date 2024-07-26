<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

final class CannotWriteCacheException extends \RuntimeException
{
    private function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function jsonException(\JsonException $exception): self
    {
        return new self(
            \sprintf('Cannot encode cache to JSON, error: "%s".', $exception->getMessage()),
            $exception->getCode(),
            $exception
        );
    }

    public static function locationIsDirectory(string $path): self
    {
        return new self(\sprintf('Cannot write cache file "%s" as the location exists as directory.', $path));
    }

    public static function locationIsNotWritable(string $path): self
    {
        return new self(\sprintf('Cannot write to file "%s" as it is not writable.', $path));
    }

    public static function missingDirectory(string $path): self
    {
        return new self(\sprintf('Directory of cache file "%s" does not exists.', $path));
    }
}
