<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\File;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\File\FileHelper;

final class FileHelperTest extends TestCase
{
    /**
     * @dataProvider detectEOLDataProvider
     */
    public function testDetectEOL(
        string $content,
        string $expected,
    ): void {
        static::assertSame($expected, FileHelper::detectEOL($content));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function detectEOLDataProvider(): iterable
    {
        yield ['foo', \PHP_EOL];
        yield ["foo\n", "\n"];
        yield ["foo\r\n", "\r\n"];
    }

    public function testNormalizePath(): void
    {
        static::assertSame('foo/bar/baz', FileHelper::normalizePath('foo/bar\baz', '/'));
        static::assertSame('foo\bar\baz', FileHelper::normalizePath('foo/bar\baz', '\\'));
    }

    /**
     * @dataProvider getRelativePathToDataProvider
     */
    public function testGetRelativePathTo(string $directoryName, string $file, string $expected): void
    {
        static::assertSame($expected, FileHelper::getRelativePath($file, $directoryName));
    }

    /**
     * @return iterable<array-key, array{string, string, string}>
     */
    public static function getRelativePathToDataProvider(): iterable
    {
        $slash = \DIRECTORY_SEPARATOR;

        yield ['', 'foo.php', 'foo.php'];
        yield ['', '/foo.php', $slash.'foo.php'];
        yield ['', '\foo.php', $slash.'foo.php'];
        yield ['directory', 'foo.php', 'foo.php'];
        yield ['directory', 'directory.php', 'directory.php'];
        yield ['directory', 'directory/foo.php', 'foo.php'];
        yield ['directory', 'directory\foo.php', 'foo.php'];
        yield ['directory', '/foo.php', $slash.'foo.php'];
        yield ['directory', '\foo.php', $slash.'foo.php'];
        yield ['directory', '/directory/foo.php', $slash.'directory'.$slash.'foo.php'];
        yield ['directory', '\directory\foo.php', $slash.'directory'.$slash.'foo.php'];
    }

    /**
     * @dataProvider removeDotDataProvider
     */
    public function testRemoveDot(
        string $fileName,
        string $expected,
    ): void {
        static::assertSame($expected, FileHelper::removeDot($fileName));
    }

    /**
     * @return iterable<array-key, array{string, string}>
     */
    public static function removeDotDataProvider(): iterable
    {
        yield ['file.twig', 'file.twig'];
        yield ['.file.twig', 'file.twig'];
        yield ['..file.twig', '.file.twig'];
    }

    /**
     * @param array<string> $ignoredDir
     *
     * @dataProvider getFileNameDataProvider
     */
    public function testGetFileName(
        string $path,
        ?string $baseDir,
        array $ignoredDir,
        ?string $expected,
    ): void {
        static::assertSame($expected, FileHelper::getFileName($path, $baseDir, $ignoredDir));

        $windowsPath = str_replace('/', '\\', $path);
        static::assertSame($expected, FileHelper::getFileName($windowsPath, $baseDir, $ignoredDir));
    }

    /**
     * @return iterable<array-key, array{string, string|null, array<string>, string|null}>
     */
    public static function getFileNameDataProvider(): iterable
    {
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            null,
            [],
            'file.twig',
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__,
            [],
            'file.twig',
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            ['directory'],
            null,
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            ['directory/'],
            null,
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            ['dir'],
            'file.twig',
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            null,
            ['directory'],
            'file.twig',
        ];
        yield [
            str_replace('/', '\\', __DIR__.'/Fixtures/directory/file.twig'), // To simulate Windows
            null,
            ['directory'],
            'file.twig',
        ];
    }

    /**
     * @param array<string> $ignoredDir
     * @param array<string> $expected
     *
     * @dataProvider getDirectoriesDataProvider
     */
    public function testGetDirectories(
        string $path,
        ?string $baseDir,
        array $ignoredDir,
        array $expected,
    ): void {
        static::assertSame($expected, FileHelper::getDirectories($path, $baseDir, $ignoredDir, __DIR__));

        $windowsPath = str_replace('/', '\\', $path);
        static::assertSame($expected, FileHelper::getDirectories($windowsPath, $baseDir, $ignoredDir, __DIR__));
    }

    /**
     * @return iterable<array-key, array{string, string|null, array<string>, array<string>}>
     */
    public static function getDirectoriesDataProvider(): iterable
    {
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__,
            [],
            ['Fixtures', 'directory'],
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            [],
            ['directory'],
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            'Fixtures',
            [],
            ['directory'],
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            ['directory'],
            [],
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            ['directory/'],
            [],
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__.'/Fixtures',
            ['dir'],
            ['directory'],
        ];
        yield [
            __DIR__.'/Fixtures/directory/file.twig',
            __DIR__,
            ['directory'],
            ['Fixtures', 'directory'],
        ];

        yield [
            '/foo_directory/directory/file.twig',
            '/',
            ['foo_directory'],
            [],
        ];
        yield [
            '/directory/foo_directory/file.twig',
            '/directory',
            [],
            ['foo_directory'],
        ];
        yield [
            './foo_directory/directory/file.twig',
            'foo_directory',
            [],
            ['directory'],
        ];
        yield [
            'foo_directory/directory/file.twig',
            'foo_directory',
            [],
            ['directory'],
        ];
        yield [
            'foo_directory/directory/file.twig',
            'foo',
            [],
            [],
        ];
    }
}
