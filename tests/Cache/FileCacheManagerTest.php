<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\Directory;
use TwigCsFixer\Cache\FileCacheManager;
use TwigCsFixer\Cache\FileHandlerInterface;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Ruleset\Ruleset;

class FileCacheManagerTest extends TestCase
{
    public function testNeedFixing(): void
    {
        $cacheManager = new FileCacheManager(
            $this->createStub(FileHandlerInterface::class),
            new Signature('8.0', '1', new Ruleset()),
            new Directory('')
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

        $signature = new Signature('8.0', '1', new Ruleset());
        $cache = new Cache($signature);
        $cache->set($file, md5($content));

        $fileHandler = $this->createStub(FileHandlerInterface::class);
        $fileHandler->method('read')->willReturn($cache);

        $cacheManager = new FileCacheManager(
            $fileHandler,
            $signature,
            new Directory('')
        );

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

        $cache = new Cache(new Signature('8.0', '1', new Ruleset()));
        $cache->set($file, md5($content));

        $fileHandler = $this->createStub(FileHandlerInterface::class);
        $fileHandler->method('read')->willReturn($cache);

        $cacheManager = new FileCacheManager(
            $fileHandler,
            new Signature('8.0', '1.1', new Ruleset()),
            new Directory('')
        );

        static::assertTrue($cacheManager->needFixing($file, $content));
    }

    public function testCannotSerialize(): void
    {
        $cacheManager = new FileCacheManager(
            $this->createStub(FileHandlerInterface::class),
            new Signature('8.0', '1', new Ruleset()),
            new Directory('')
        );

        $this->expectException(BadMethodCallException::class);
        serialize($cacheManager);
    }

    public function testCannotUnserialize(): void
    {
        $this->expectException(BadMethodCallException::class);
        unserialize('O:34:"TwigCsFixer\Cache\FileCacheManager":0:{}');
    }
}
