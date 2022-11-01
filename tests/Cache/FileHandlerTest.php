<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\FileHandler;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Ruleset\Ruleset;

class FileHandlerTest extends TestCase
{
    /**
     * @dataProvider readDataProvider
     */
    public function testRead(string $file, bool $isFound): void
    {
        $fileHandler = new FileHandler($file);

        if ($isFound) {
            static::assertNotNull($fileHandler->read());
        } else {
            static::assertNull($fileHandler->read());
        }
    }

    /**
     * @return iterable<array-key, array{string, bool}>
     */
    public function readDataProvider(): iterable
    {
        yield ['foo.php', false];
        yield [__FILE__, false];
        yield [__DIR__.\DIRECTORY_SEPARATOR.'Fixtures/cache', true];
    }

    /**
     * @dataProvider writeFailureDataProvider
     */
    public function testWriteFailure(string $file): void
    {
        $fileHandler = new FileHandler($file);

        $this->expectException(RuntimeException::class);
        $fileHandler->write(new Cache(new Signature('8.0', '1', new Ruleset())));
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public function writeFailureDataProvider(): iterable
    {
        yield ['/fakeDir/foo.php'];
        yield [__DIR__];
        yield [__DIR__.\DIRECTORY_SEPARATOR.'Fixtures/notWritable'];
    }
}
