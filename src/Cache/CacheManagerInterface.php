<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

/**
 * @internal
 */
interface CacheManagerInterface
{
    public function needFixing(string $file, string $fileContent): bool;

    public function setFile(string $file, string $fileContent): void;
}
