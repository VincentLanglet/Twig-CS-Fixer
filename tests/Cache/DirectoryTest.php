<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Directory;

final class DirectoryTest extends TestCase
{
    /**
     * @dataProvider getRelativePathToDataProvider
     */
    public function testGetRelativePathTo(string $directoryName, string $file, string $expected): void
    {
        $directory = new Directory($directoryName);
        static::assertSame($expected, $directory->getRelativePathTo($file));
    }

    /**
     * @return iterable<array-key, array{string, string, string}>
     */
    public function getRelativePathToDataProvider(): iterable
    {
        yield ['', 'foo.php', 'foo.php'];
        yield ['', '/foo.php', '/foo.php'];
        yield ['', '\foo.php', '/foo.php'];
        yield ['directory', 'foo.php', 'foo.php'];
        yield ['directory', 'directory.php', 'directory.php'];
        yield ['directory', 'directory/foo.php', 'foo.php'];
        yield ['directory', 'directory\foo.php', 'foo.php'];
        yield ['directory', '/foo.php', '/foo.php'];
        yield ['directory', '\foo.php', '/foo.php'];
        yield ['directory', '/directory/foo.php', '/directory/foo.php'];
        yield ['directory', '\directory\foo.php', '/directory/foo.php'];
    }
}
