<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\Manager;

final class NullCacheManager implements CacheManagerInterface
{
    public function needFixing(string $file, string $fileContent): bool
    {
        return true;
    }

    public function setFile(string $file, string $fileContent): void
    {
    }
}
