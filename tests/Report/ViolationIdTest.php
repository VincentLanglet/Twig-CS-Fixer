<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\ViolationId;

final class ViolationIdTest extends TestCase
{
    /**
     * @dataProvider toStringDataProvider
     */
    #[DataProvider('toStringDataProvider')]
    public function testToString(
        ?string $ruleShortName,
        ?string $identifier,
        ?int $line,
        ?int $linePosition,
        string $expected,
    ): void {
        $violationId = new ViolationId(
            $ruleShortName,
            $identifier,
            $line,
            $linePosition
        );
        static::assertSame($expected, $violationId->toString());
    }

    /**
     * @return iterable<array-key, array{string|null, string|null, int|null, int|null, string}>
     */
    public static function toStringDataProvider(): iterable
    {
        yield [null, null, null, null, ''];
        yield ['short', null, null, null, 'short'];
        yield ['short', 'id', null, null, 'short.id'];
        yield ['short', null, 1, null, 'short:1'];
        yield ['short', null, null, 1, 'short::1'];
        yield ['short', 'id', 1, null, 'short.id:1'];
        yield ['short', 'id', 1, 1, 'short.id:1:1'];
    }
}
