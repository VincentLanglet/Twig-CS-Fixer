<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use RuntimeException;

interface FileHandlerInterface
{
    public function getFile(): string;

    public function read(): ?CacheInterface;

    /**
     * @throws RuntimeException
     */
    public function write(CacheInterface $cache): void;
}
