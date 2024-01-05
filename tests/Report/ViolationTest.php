<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;

final class ViolationTest extends TestCase
{
    public function testGetters(): void
    {
        $violationId = new ViolationId('name');
        $violation = new Violation(Violation::LEVEL_WARNING, 'message', 'filename', 42, 33, 'name', $violationId);

        static::assertSame(Violation::LEVEL_WARNING, $violation->getLevel());
        static::assertSame('message', $violation->getMessage());
        static::assertSame('filename', $violation->getFilename());
        static::assertSame(42, $violation->getLine());
        static::assertSame(33, $violation->getLinePosition());
        static::assertSame('name', $violation->getRuleName());
        static::assertSame($violationId, $violation->getIdentifier());
    }

    /**
     * @dataProvider getLevelAsStringDataProvider
     */
    public function testGetLevelAsString(string $expected, int $level): void
    {
        static::assertSame($expected, Violation::getLevelAsString($level));
    }

    /**
     * @return iterable<array-key, array{string, int}>
     */
    public static function getLevelAsStringDataProvider(): iterable
    {
        yield ['NOTICE', 0];
        yield ['WARNING', 1];
        yield ['ERROR', 2];
        yield ['FATAL', 3];
    }

    public function testGetLevelAsStringException(): void
    {
        $this->expectExceptionMessage('Level "4" is not supported.');
        Violation::getLevelAsString(4);
    }

    /**
     * @dataProvider getLevelAsIntDataProvider
     */
    public function testGetLevelAsInt(int $expected, string $level): void
    {
        static::assertSame($expected, Violation::getLevelAsInt($level));
    }

    /**
     * @return iterable<array-key, array{int, string}>
     */
    public static function getLevelAsIntDataProvider(): iterable
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
        $this->expectExceptionMessage('Level "Yolo" is not supported.');
        Violation::getLevelAsInt('Yolo');
    }
}
