<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

interface CacheInterface
{
    public function getSignature(): SignatureInterface;

    public function has(string $file): bool;

    public function get(string $file): ?int;

    public function set(string $file, int $hash): void;

    public function clear(string $file): void;

    public function toJson(): string;
}
