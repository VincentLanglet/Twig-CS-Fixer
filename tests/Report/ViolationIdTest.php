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
        string $ruleShortName,
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
     * @return iterable<array-key, array{string, string|null, string|null, int|null, int|null, string}>
     */
    public static function toStringDataProvider(): iterable
    {
        yield ['short', null, null, null, null, 'short'];
        yield ['short', 'id', null, null, null, 'short.id'];
        yield ['short', null, 'token', null, null, 'short..token'];
        yield ['short', null, null, 1, null, 'short:1'];
        yield ['short', null, null, null, 1, 'short::1'];
        yield ['short', 'id', 'token', null, null, 'short.id.token'];
        yield ['short', 'id', 'token', 1, null, 'short.id.token:1'];
        yield ['short', 'id', 'token', 1, 1, 'short.id.token:1:1'];
    }
}
