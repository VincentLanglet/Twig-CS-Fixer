<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\ViolationId;

class ViolationIdTest extends TestCase
{
    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(
        ?string $ruleShortName,
        ?string $identifier,
        ?string $tokenName,
        ?int $line,
        ?int $linePosition,
        string $expected,
    ): void {
        $violationId = new ViolationId(
            $ruleShortName,
            $identifier,
            $tokenName,
            $line,
            $linePosition
        );
        static::assertSame($expected, $violationId->toString());

        $fromString = ViolationId::fromString($expected);
        static::assertTrue($fromString->match($violationId));
        static::assertTrue($violationId->match($fromString));
    }

    /**
     * @return iterable<array-key, array{string|null, string|null, string|null, int|null, int|null, string}>
     */
    public static function toStringDataProvider(): iterable
    {
        yield [null, null, null, null, null, ''];
        yield ['short', null, null, null, null, 'short'];
        yield ['short', 'id', null, null, null, 'short.id'];
        yield ['short', null, 'token', null, null, 'short..token'];
        yield ['short', null, null, 1, null, 'short:1'];
        yield ['short', null, null, null, 1, 'short::1'];
        yield ['short', 'id', 'token', null, null, 'short.id.token'];
        yield ['short', 'id', 'token', 1, null, 'short.id.token:1'];
        yield ['short', 'id', 'token', 1, 1, 'short.id.token:1:1'];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(string $string1, string $string2, bool $expected): void
    {
        $violationId1 = ViolationId::fromString($string1);
        $violationId2 = ViolationId::fromString($string2);
        static::assertSame($expected, $violationId1->match($violationId2));
    }

    /**
     * @return iterable<array-key, array{string, string, bool}>
     */
    public static function matchDataProvider(): iterable
    {
        yield ['', 'short', true];
        yield ['', 'short.id.token:1:1', true];
        yield ['short', 'short', true];
        yield ['short', 'short.id.token:1:1', true];
        yield ['short.id', 'short.id.token:1:1', true];
        yield ['short.notId', 'short.id.token:1:1', false];
        yield ['short.id', 'short', false];
        yield ['SHORT.ID.TOKEN', 'short.id.token:1:1', true];
        yield ['short.id.token:2:1', 'short.id.token:1:1', false];
        yield ['short.id.token:1:2', 'short.id.token:1:1', false];
        yield ['short::1', 'short.id.token:1:1', true];
        yield ['short.id::1', 'short.id.token:1:1', true];
        yield ['short.id.token::1', 'short.id.token:1:1', true];
    }
}
