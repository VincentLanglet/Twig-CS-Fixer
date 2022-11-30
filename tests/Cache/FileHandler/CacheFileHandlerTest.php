<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache\FileHandler;

use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\FileHandler\CacheFileHandler;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Exception\CannotWriteCacheException;
use TwigCsFixer\Tests\FileTestCase;

class CacheFileHandlerTest extends FileTestCase
{
    /**
     * @dataProvider readFailureDataProvider
     */
    public function testReadFailure(string $file): void
    {
        $cacheFileHandler = new CacheFileHandler($file);
        static::assertNull($cacheFileHandler->read());
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public function readFailureDataProvider(): iterable
    {
        yield ['foo.php'];
        yield [__FILE__];
    }

    public function testReadFailurePermission(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/notReadable');
        chmod($file, 0222);

        $cacheFileHandler = new CacheFileHandler($file);
        static::assertNull($cacheFileHandler->read());
    }

    public function testReadSuccess(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/readable');

        $cacheFileHandler = new CacheFileHandler($file);
        static::assertNotNull($cacheFileHandler->read());
    }

    public function testWriteFailureMissingDirectory(): void
    {
        $cacheFileHandler = new CacheFileHandler('/fakeDir/foo.php');

        $this->expectExceptionObject(CannotWriteCacheException::missingDirectory('/fakeDir/foo.php'));
        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', [])));
    }

    public function testWriteFailureInDirectory(): void
    {
        $dir = $this->getTmpPath(__DIR__.'/Fixtures');
        $cacheFileHandler = new CacheFileHandler($dir);

        $this->expectExceptionObject(CannotWriteCacheException::locationIsDirectory($dir));
        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', [])));
    }

    public function testWriteFailurePermission(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/notWritable');
        chmod($file, 0444);
        $cacheFileHandler = new CacheFileHandler($file);

        $this->expectExceptionObject(CannotWriteCacheException::locationIsNotWritable($file));
        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', [])));
    }

    public function testWriteFailureEncoding(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/writable');
        $cacheFileHandler = new CacheFileHandler($file);

        $this->expectException(CannotWriteCacheException::class);
        $cacheFileHandler->write(new Cache(new Signature('8.0', "\xB1\x31", [])));
    }

    public function testWriteSuccess(): void
    {
        $file = $this->getTmpPath(__DIR__.'/Fixtures/writable');
        unlink($file);
        $cacheFileHandler = new CacheFileHandler($file);

        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', [])));
        $content = file_get_contents($file);
        static::assertSame('{"php_version":"8.0","fixer_version":"1","sniffs":[],"hashes":[]}', $content);

        $permissions = fileperms($file);
        static::assertNotFalse($permissions);
        static::assertSame('0666', substr(sprintf('%o', $permissions), -4));
    }
}
