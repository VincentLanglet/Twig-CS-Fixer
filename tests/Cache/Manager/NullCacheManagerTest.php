<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache\Manager;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Manager\NullCacheManager;

final class NullCacheManagerTest extends TestCase
{
    public function testNeedFixing(): void
    {
        $cacheManager = new NullCacheManager();

        $file = 'foo.php';
        $content = 'foo';

        static::assertTrue($cacheManager->needFixing($file, $content));
        $cacheManager->setFile($file, $content);
        static::assertTrue($cacheManager->needFixing($file, $content));
    }
}
