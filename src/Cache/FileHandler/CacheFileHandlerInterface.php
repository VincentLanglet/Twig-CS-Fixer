<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\FileHandler;

use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Exception\CannotWriteCacheException;

interface CacheFileHandlerInterface
{
    public function getFile(): string;

    public function read(): ?Cache;

    /**
     * @throws CannotWriteCacheException
     */
    public function write(Cache $cache): void;
}
