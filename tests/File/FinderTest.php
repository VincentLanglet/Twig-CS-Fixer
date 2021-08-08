<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\File;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\File\Finder;

/**
 * Test for Finder.
 */
class FinderTest extends TestCase
{
    /**
     * @return void
     */
    public function testWithNoPath(): void
    {
        $finder = new Finder();
        self::assertSame([], $finder->findFiles());
    }

    /**
     * @return void
     */
    public function testWithWrongPath(): void
    {
        $finder = new Finder([__DIR__.'/data/template_not_found.twig']);

        self::expectExceptionMessage('Unknown path');
        $finder->findFiles();
    }

    /**
     * @return void
     */
    public function testWithPath(): void
    {
        $finder = new Finder([__DIR__.'/data/template.twig']);
        self::assertSame([__DIR__.'/data/template.twig'], $finder->findFiles());
    }

    /**
     * @return void
     */
    public function testWithDirectory(): void
    {
        $finder = new Finder([__DIR__.'/data']);

        $files = $finder->findFiles();
        self::assertCount(3, $files);
        self::assertContains(__DIR__.'/data/template.twig', $files);
        self::assertContains(__DIR__.'/data/directory/template.twig', $files);
        self::assertContains(__DIR__.'/data/directory/subdirectory/template.twig', $files);
    }
}
