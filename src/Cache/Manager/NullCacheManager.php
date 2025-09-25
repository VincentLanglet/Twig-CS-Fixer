<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\Manager;

/**
 * This file was copied (and slightly modified) from PHP CS Fixer:
 * - https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/src/Cache/NullCacheManager.php
 * - (c) Fabien Potencier <fabien@symfony.com>, Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * - For the full copyright and license information, please see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/LICENSE
 */
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
