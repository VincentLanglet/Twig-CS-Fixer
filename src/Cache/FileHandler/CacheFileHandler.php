<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\FileHandler;

use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\CacheEncoder;
use TwigCsFixer\Exception\CannotWriteCacheException;

final class CacheFileHandler implements CacheFileHandlerInterface
{
    public function __construct(private string $file)
    {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function read(): ?Cache
    {
        if (!file_exists($this->file)) {
            return null;
        }

        $content = @file_get_contents($this->file);
        if (false === $content) {
            return null;
        }

        try {
            return CacheEncoder::fromJson($content);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function write(Cache $cache): void
    {
        if (file_exists($this->file)) {
            if (is_dir($this->file)) {
                throw CannotWriteCacheException::locationIsDirectory($this->file);
            }

            if (!is_writable($this->file)) {
                throw CannotWriteCacheException::locationIsNotWritable($this->file);
            }
        } else {
            $dir = \dirname($this->file);

            if (!is_dir($dir) && !@mkdir($dir, recursive: true)) {
                throw CannotWriteCacheException::missingDirectory($this->file);
            }

            @touch($this->file);
            @chmod($this->file, 0666);
        }

        try {
            file_put_contents($this->file, CacheEncoder::toJson($cache));
        } catch (\JsonException $exception) {
            throw CannotWriteCacheException::jsonException($exception);
        }
    }
}
