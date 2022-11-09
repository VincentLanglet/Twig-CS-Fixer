<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\FileHandler;

use InvalidArgumentException;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\CacheEncoder;
use TwigCsFixer\Exception\CannotWriteCacheException;
use UnexpectedValueException;

final class CacheFileHandler implements CacheFileHandlerInterface
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
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
        } catch (InvalidArgumentException $e) {
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

            if (!is_dir($dir)) {
                throw CannotWriteCacheException::missingDirectory($this->file);
            }

            @touch($this->file);
            @chmod($this->file, 0666);
        }

        try {
            file_put_contents($this->file, CacheEncoder::toJson($cache));
        } catch (UnexpectedValueException $exception) {
            throw CannotWriteCacheException::because($exception);
        }
    }
}
