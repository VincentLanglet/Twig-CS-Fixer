<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\File;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\File\FileHelper;

final class FileHelperTest extends TestCase
{
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
        string $absolutePath,
        ?string $baseDir,
        array $ignoredDir,
        ?string $expected,
    ): void {
        static::assertSame($expected, FileHelper::getFileName($absolutePath, $baseDir, $ignoredDir));
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
            ['/directory'],
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
    }

    /**
     * @param array<string> $ignoredDir
     * @param array<string> $expected
     *
     * @dataProvider getDirectoriesDataProvider
     */
    public function testGetDirectories(
        string $absolutePath,
        ?string $baseDir,
        array $ignoredDir,
        array $expected,
    ): void {
        static::assertSame($expected, FileHelper::getDirectories($absolutePath, $baseDir, $ignoredDir));
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
            ['/directory'],
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
            __DIR__.'/directory/foo/directory/file.twig',
            'directory',
            [],
            [],
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
    }
}
