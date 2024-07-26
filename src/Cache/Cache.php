<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

final class Cache
{
    /**
     * @var array<string, string>
     */
    private array $hashes = [];

    public function __construct(private Signature $signature)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getHashes(): array
    {
        return $this->hashes;
    }

    public function getSignature(): Signature
    {
        return $this->signature;
    }

    public function has(string $file): bool
    {
        return \array_key_exists($file, $this->hashes);
    }

    public function get(string $file): string
    {
        if (!$this->has($file)) {
            throw new \InvalidArgumentException(\sprintf('The file "%s" is not cached', $file));
        }

        return $this->hashes[$file];
    }

    public function set(string $file, string $hash): void
    {
        $this->hashes[$file] = $hash;
    }

    public function clear(string $file): void
    {
        unset($this->hashes[$file]);
    }
}
