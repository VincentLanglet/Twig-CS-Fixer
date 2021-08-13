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
        $finder = new Finder([__DIR__.'/Fixtures/template_not_found.twig']);

        self::expectExceptionMessage('Unknown path');
        $finder->findFiles();
    }

    /**
     * @return void
     */
    public function testWithPath(): void
    {
        $finder = new Finder([__DIR__.'/Fixtures/template.twig']);
        self::assertSame([__DIR__.'/Fixtures/template.twig'], $finder->findFiles());
    }

    /**
     * @return void
     */
    public function testWithDirectory(): void
    {
        $finder = new Finder([__DIR__.'/Fixtures']);

        $files = $finder->findFiles();
        self::assertCount(3, $files);
        self::assertContains(__DIR__.'/Fixtures/template.twig', $files);
        self::assertContains(__DIR__.'/Fixtures/directory/template.twig', $files);
        self::assertContains(__DIR__.'/Fixtures/directory/subdirectory/template.twig', $files);
    }
}
