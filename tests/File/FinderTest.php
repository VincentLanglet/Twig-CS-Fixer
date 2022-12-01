<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\File;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\File\Finder;

class FinderTest extends TestCase
{
    public function testFinder(): void
    {
        $finder = new Finder();

        $finder->in(__DIR__.'/Fixtures');
        static::assertCount(2, $finder);

        $finder->ignoreDotFiles(false);
        static::assertCount(3, $finder);
    }
}
