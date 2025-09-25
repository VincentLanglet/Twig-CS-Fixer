<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache\Manager;

use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\FileHandler\CacheFileHandlerInterface;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Exception\CannotWriteCacheException;
use TwigCsFixer\File\FileHelper;

/**
 * This file was copied (and slightly modified) from PHP CS Fixer:
 * - https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/src/Cache/FileCacheManager.php
 * - (c) Fabien Potencier <fabien@symfony.com>, Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * - For the full copyright and license information, please see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/LICENSE
 */
final class FileCacheManager implements CacheManagerInterface
{
    private Cache $cache;

    private string $cacheDirectory;

    public function __construct(
        private CacheFileHandlerInterface $handler,
        private Signature $signature,
    ) {
        $this->cacheDirectory = \dirname($handler->getFile());

        $this->readCache();
    }

    /**
     * @throws CannotWriteCacheException
     */
    public function __destruct()
    {
        $this->writeCache();
    }

    /**
     * This class is not intended to be serialized, and cannot be deserialized.
     */
    public function __sleep(): array
    {
        throw new \BadMethodCallException(\sprintf('Cannot serialize %s.', self::class));
    }

    /**
     * Prevent attacker executing code by leveraging the __destruct method.
     *
     * @see https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection
     */
    public function __wakeup(): void
    {
        throw new \BadMethodCallException(\sprintf('Cannot unserialize %s.', self::class));
    }

    public function needFixing(string $file, string $fileContent): bool
    {
        $file = FileHelper::getRelativePath($file, $this->cacheDirectory);

        return !$this->cache->has($file) || $this->cache->get($file) !== $this->calcHash($fileContent);
    }

    public function setFile(string $file, string $fileContent): void
    {
        $file = FileHelper::getRelativePath($file, $this->cacheDirectory);

        $hash = $this->calcHash($fileContent);

        $this->cache->set($file, $hash);
    }

    private function readCache(): void
    {
        $cache = $this->handler->read();

        if (null === $cache || !$this->signature->equals($cache->getSignature())) {
            $cache = new Cache($this->signature);
        }

        $this->cache = $cache;
    }

    /**
     * @throws CannotWriteCacheException
     */
    private function writeCache(): void
    {
        $this->handler->write($this->cache);
    }

    private function calcHash(string $content): string
    {
        return md5($content);
    }
}
