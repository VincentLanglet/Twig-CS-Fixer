<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\CacheFileHandler;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Ruleset\Ruleset;

class FileHandlerTest extends TestCase
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
        $file = __DIR__.'/Fixtures/notReadable';
        chmod($file, 0222);
        $cacheFileHandler = new CacheFileHandler($file);

        set_error_handler(fn (): bool => true);
        static::assertNull($cacheFileHandler->read());
        restore_error_handler();

        // Restore permissions
        chmod($file, 0644);
    }

    public function testReadSuccess(): void
    {
        $cacheFileHandler = new CacheFileHandler(__DIR__.'/Fixtures/readable');
        static::assertNotNull($cacheFileHandler->read());
    }

    /**
     * @dataProvider writeFailureDataProvider
     */
    public function testWriteFailure(string $file): void
    {
        $cacheFileHandler = new CacheFileHandler($file);

        $this->expectException(RuntimeException::class);
        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', new Ruleset())));
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public function writeFailureDataProvider(): iterable
    {
        yield ['/fakeDir/foo.php'];
        yield [__DIR__];
    }

    public function testWriteFailurePermission(): void
    {
        $file = __DIR__.'/Fixtures/notWritable';
        chmod($file, 0444);
        $cacheFileHandler = new CacheFileHandler($file);

        $this->expectException(RuntimeException::class);
        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', new Ruleset())));

        // Restore permissions
        chmod($file, 0644);
    }

    public function testWriteSuccess(): void
    {
        $file = __DIR__.'/Fixtures/writable';
        unlink($file);
        $cacheFileHandler = new CacheFileHandler($file);

        $cacheFileHandler->write(new Cache(new Signature('8.0', '1', new Ruleset())));
        $content = file_get_contents($file);
        static::assertSame('{"php_version":"8.0","fixer_version":"1","sniffs":[],"hashes":[]}', $content);

        $permissions = fileperms($file);
        static::assertNotFalse($permissions);
        static::assertSame('0666', substr(sprintf('%o', $permissions), -4));
    }
}
