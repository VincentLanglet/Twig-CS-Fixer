<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\FileHandler;

class FileHandlerTest extends TestCase
{
    /**
     * @dataProvider getRelativePathToDataProvider
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
    public function getRelativePathToDataProvider(): iterable
    {
        yield ['foo.php', false];
        yield [__FILE__, false];
        yield [__DIR__.\DIRECTORY_SEPARATOR.'Fixtures/cache', true];
    }
}
