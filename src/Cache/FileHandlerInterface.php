<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

interface FileHandlerInterface
{
    public function getFile(): string;

    public function read(): ?CacheInterface;

    public function write(CacheInterface $cache): void;
}
