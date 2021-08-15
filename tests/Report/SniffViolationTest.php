<?php

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\SniffViolation;

/**
 * Test for SniffViolation.
 */
class SniffViolationTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetters(): void
    {
        $sniffViolation = new SniffViolation(SniffViolation::LEVEL_WARNING, 'message', 'filename', 42);
        self::assertSame(SniffViolation::LEVEL_WARNING, $sniffViolation->getLevel());
        self::assertSame('message', $sniffViolation->getMessage());
        self::assertSame('filename', $sniffViolation->getFilename());
        self::assertSame(42, $sniffViolation->getLine());

        $sniffViolation->setLinePosition(33);
        self::assertSame(33, $sniffViolation->getLinePosition());
    }

    /**
     * @param string $expected
     * @param int    $level
     *
     * @return void
     *
     * @dataProvider getLevelAsStringDataProvider
     */
    public function testGetLevelAsString(string $expected, int $level): void
    {
        $sniffViolation = new SniffViolation($level, 'foo', 'bar');
        self::assertSame($expected, $sniffViolation->getLevelAsString());
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

    /**
     * @return void
     */
    public function testGetLevelAsStringException(): void
    {
        $sniffViolation = new SniffViolation(4, 'foo', 'bar');
        self::expectExceptionMessage('Level "4" is not supported.');
        $sniffViolation->getLevelAsString();
    }

    /**
     * @param int    $expected
     * @param string $level
     *
     * @return void
     *
     * @dataProvider getLevelAsIntDataProvider
     */
    public function testGetLevelAsInt(int $expected, string $level): void
    {
        self::assertSame($expected, SniffViolation::getLevelAsInt($level));
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

    /**
     * @return void
     */
    public function testGetLevelAsIntException(): void
    {
        self::expectExceptionMessage('Level "Yolo" is not supported.');
        SniffViolation::getLevelAsInt('Yolo');
    }
}
