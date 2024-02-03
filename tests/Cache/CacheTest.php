<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\Signature;

final class CacheTest extends TestCase
{
    public function testCache(): void
    {
        $signature = new Signature('7.4', '1', []);
        $cache = new Cache($signature);
        static::assertSame($signature, $cache->getSignature());
        static::assertSame([], $cache->getHashes());

        $cache->set('fooFile', 'fooHash');
        static::assertTrue($cache->has('fooFile'));
        static::assertFalse($cache->has('barFile'));
        static::assertSame('fooHash', $cache->get('fooFile'));

        $cache->set('barFile', 'barHash');
        static::assertTrue($cache->has('barFile'));

        static::assertSame(
            ['fooFile' => 'fooHash', 'barFile' => 'barHash'],
            $cache->getHashes()
        );
        $cache->set('fooFile', 'fooHash2');
        static::assertTrue($cache->has('fooFile'));
        static::assertSame('fooHash2', $cache->get('fooFile'));
        static::assertSame(
            ['fooFile' => 'fooHash2', 'barFile' => 'barHash'],
            $cache->getHashes()
        );

        $cache->clear('fooFile');
        static::assertFalse($cache->has('fooFile'));
        static::assertSame(
            ['barFile' => 'barHash'],
            $cache->getHashes()
        );
    }

    public function testCacheFileNotFound(): void
    {
        $this->expectException(\LogicException::class);

        $signature = new Signature('7.4', '1', []);
        $cache = new Cache($signature);
        $cache->get('notFound');
    }
}
