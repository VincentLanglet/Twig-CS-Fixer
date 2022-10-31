<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

interface CacheInterface
{
    public function getSignature(): SignatureInterface;

    public function has(string $file): bool;

    public function get(string $file): string;

    public function set(string $file, string $hash): void;

    public function clear(string $file): void;

    /**
     * @return array<string, string>
     */
    public function getHashes(): array;
}
