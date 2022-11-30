<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache\Manager;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\FileHandler\CacheFileHandlerInterface;
use TwigCsFixer\Cache\Manager\FileCacheManager;
use TwigCsFixer\Cache\Signature;

final class FileCacheManagerTest extends TestCase
{
    public function testNeedFixing(): void
    {
        $cacheManager = new FileCacheManager(
            $this->createStub(CacheFileHandlerInterface::class),
            new Signature('8.0', '1', [])
        );

        $file = 'foo.php';
        $content = 'foo';

        static::assertTrue($cacheManager->needFixing($file, $content));
        $cacheManager->setFile($file, $content);
        static::assertFalse($cacheManager->needFixing($file, $content));
    }

    public function testNeedFixingWithCache(): void
    {
        $file = 'foo.php';
        $content = 'foo';

        $signature = new Signature('8.0', '1', []);
        $cache = new Cache($signature);
        $cache->set($file, md5($content));

        $cacheFileHandler = $this->createStub(CacheFileHandlerInterface::class);
        $cacheFileHandler->method('read')->willReturn($cache);

        $cacheManager = new FileCacheManager($cacheFileHandler, $signature);

        $anotherFile = 'bar.php';
        $newContent = 'bar';

        static::assertFalse($cacheManager->needFixing($file, $content));
        static::assertTrue($cacheManager->needFixing($anotherFile, $content));
        static::assertTrue($cacheManager->needFixing($file, $newContent));
    }

    public function testNeedFixingWithOutdatedCache(): void
    {
        $file = 'foo.php';
        $content = 'foo';

        $cache = new Cache(new Signature('8.0', '1', []));
        $cache->set($file, md5($content));

        $cacheFileHandler = $this->createStub(CacheFileHandlerInterface::class);
        $cacheFileHandler->method('read')->willReturn($cache);

        $cacheManager = new FileCacheManager(
            $cacheFileHandler,
            new Signature('8.0', '1.1', [])
        );

        static::assertTrue($cacheManager->needFixing($file, $content));
    }

    public function testDestructWriteCache(): void
    {
        $cacheFileHandler = $this->createMock(CacheFileHandlerInterface::class);
        $cacheFileHandler->expects(static::once())->method('write');

        $cacheManager = new FileCacheManager(
            $cacheFileHandler,
            new Signature('8.0', '1.1', [])
        );
        unset($cacheManager); // Trigger the __destruct method
    }

    public function testCannotSerialize(): void
    {
        $cacheManager = new FileCacheManager(
            $this->createStub(CacheFileHandlerInterface::class),
            new Signature('8.0', '1', [])
        );

        $this->expectException(BadMethodCallException::class);
        serialize($cacheManager);
    }

    public function testCannotUnserialize(): void
    {
        $this->expectException(BadMethodCallException::class);
        unserialize('O:42:"TwigCsFixer\Cache\Manager\FileCacheManager":0:{}');
    }
}
