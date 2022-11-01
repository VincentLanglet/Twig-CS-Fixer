<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use RuntimeException;

interface CacheFileHandlerInterface
{
    public function getFile(): string;

    public function read(): ?Cache;

    /**
     * @throws RuntimeException
     */
    public function write(Cache $cache): void;
}
