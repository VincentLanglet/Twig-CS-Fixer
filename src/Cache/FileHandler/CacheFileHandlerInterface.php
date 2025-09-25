<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\FileHandler;

use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Exception\CannotWriteCacheException;

/**
 * This file was copied (and slightly modified) from PHP CS Fixer:
 * - https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/src/Cache/FileHandlerInterface.php
 * - (c) Fabien Potencier <fabien@symfony.com>, Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * - For the full copyright and license information, please see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/LICENSE
 */
interface CacheFileHandlerInterface
{
    public function getFile(): string;

    public function read(): ?Cache;

    /**
     * @throws CannotWriteCacheException
     */
    public function write(Cache $cache): void;
}
