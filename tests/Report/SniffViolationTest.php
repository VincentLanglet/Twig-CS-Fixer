<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\SniffViolation;

class SniffViolationTest extends TestCase
{
    public function testGetters(): void
    {
        $sniffViolation = new SniffViolation(SniffViolation::LEVEL_WARNING, 'message', 'filename', 42);
        static::assertSame(SniffViolation::LEVEL_WARNING, $sniffViolation->getLevel());
        static::assertSame('message', $sniffViolation->getMessage());
        static::assertSame('filename', $sniffViolation->getFilename());
        static::assertSame(42, $sniffViolation->getLine());

        $sniffViolation->setLinePosition(33);
        static::assertSame(33, $sniffViolation->getLinePosition());
    }

    /**
     * @dataProvider getLevelAsStringDataProvider
     */
    public function testGetLevelAsString(string $expected, int $level): void
    {
        static::assertSame($expected, SniffViolation::getLevelAsString($level));
    }

    /**
     * @return iterable<array-key, array{string, int}>
     */
    public function getLevelAsStringDataProvider(): iterable
    {
        yield ['NOTICE', 0];
        yield ['WARNING', 1];
        yield ['ERROR', 2];
        yield ['FATAL', 3];
    }

    public function testGetLevelAsStringException(): void
    {
        self::expectExceptionMessage('Level "4" is not supported.');
        SniffViolation::getLevelAsString(4);
    }

    /**
     * @dataProvider getLevelAsIntDataProvider
     */
    public function testGetLevelAsInt(int $expected, string $level): void
    {
        static::assertSame($expected, SniffViolation::getLevelAsInt($level));
    }

    /**
     * @return iterable<array-key, array{int, string}>
     */
    public function getLevelAsIntDataProvider(): iterable
    {
        yield [0, 'NOTICE'];
        yield [0, 'notice'];
        yield [0, 'NoTiCe'];
        yield [1, 'warning'];
        yield [2, 'error'];
        yield [3, 'fatal'];
    }

    public function testGetLevelAsIntException(): void
    {
        self::expectExceptionMessage('Level "Yolo" is not supported.');
        SniffViolation::getLevelAsInt('Yolo');
    }
}
