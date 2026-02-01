<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Report;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Report\IgnoredViolationId;
use TwigCsFixer\Report\ViolationId;

final class IgnoredViolationIdTest extends TestCase
{
    /**
     * @dataProvider matchDataProvider
     */
    #[DataProvider('matchDataProvider')]
    public function testMatch(IgnoredViolationId $ignoredViolationId, string $string2, bool $expected): void
    {
        $violationId = ViolationId::fromString($string2);
        static::assertSame($expected, $ignoredViolationId->match($violationId));
    }

    /**
     * @return iterable<array-key, array{IgnoredViolationId, string, bool}>
     */
    public static function matchDataProvider(): iterable
    {
        yield [new IgnoredViolationId(), 'short', true];
        yield [new IgnoredViolationId(), 'short.id:1:1', true];
        yield [new IgnoredViolationId('short'), 'short', true];
        yield [new IgnoredViolationId('short'), 'short.id:1:1', true];
        yield [new IgnoredViolationId('short', 'id'), 'short.id:1:1', true];
        yield [new IgnoredViolationId('short', 'notId'), 'short.id:1:1', false];
        yield [new IgnoredViolationId('short', 'id'), 'short', false];
        yield [new IgnoredViolationId('SHORT', 'ID'), 'short.id:1:1', true];
        yield [new IgnoredViolationId('short', 'id', 2, 1, 2, 1), 'short.id:1:1', false];
        yield [new IgnoredViolationId('short', 'id', 1, 2, 1, 2), 'short.id:1:1', false];
        yield [new IgnoredViolationId('short', startLinePosition: 1, endLinePosition: 1), 'short.id:1:1', true];
        yield [new IgnoredViolationId('short', 'id', startLinePosition: 1, endLinePosition: 1), 'short.id:1:1', true];

        yield [new IgnoredViolationId(startLine: 10), 'short', false];
        yield [new IgnoredViolationId(endLine: 10), 'short', false];
        yield [new IgnoredViolationId(startLinePosition: 10), 'short', false];
        yield [new IgnoredViolationId(endLinePosition: 10), 'short', false];

        yield [new IgnoredViolationId(startLine: 10, endLine: 20), 'short:10', true];
        yield [new IgnoredViolationId(startLine: 10, endLine: 20), 'short:15', true];
        yield [new IgnoredViolationId(startLine: 10, endLine: 20), 'short:20', true];
        yield [new IgnoredViolationId(startLine: 10, endLine: 20), 'short:9', false];
        yield [new IgnoredViolationId(startLine: 10, endLine: 20), 'short:21', false];
        yield [new IgnoredViolationId(startLinePosition: 10, endLinePosition: 20), 'short::10', true];
        yield [new IgnoredViolationId(startLinePosition: 10, endLinePosition: 20), 'short::15', true];
        yield [new IgnoredViolationId(startLinePosition: 10, endLinePosition: 20), 'short::20', true];
        yield [new IgnoredViolationId(startLinePosition: 10, endLinePosition: 20), 'short::9', false];
        yield [new IgnoredViolationId(startLinePosition: 10, endLinePosition: 20), 'short::21', false];

        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 20, endLine: 30, endLinePosition: 40), 'short:10:19', false];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 20, endLine: 30, endLinePosition: 40), 'short:10:20', true];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 20, endLine: 30, endLinePosition: 40), 'short:11:19', true];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 20, endLine: 30, endLinePosition: 40), 'short:29:41', true];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 20, endLine: 30, endLinePosition: 40), 'short:30:40', true];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 20, endLine: 30, endLinePosition: 40), 'short:30:41', false];

        yield [new IgnoredViolationId(startLine: 10), 'short:9', false];
        yield [new IgnoredViolationId(startLine: 10), 'short:10', true];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 10), 'short:10', false];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 10), 'short:11', true];
        yield [new IgnoredViolationId(endLine: 10), 'short:10', true];
        yield [new IgnoredViolationId(endLine: 10), 'short:11', false];
        yield [new IgnoredViolationId(endLine: 10, endLinePosition: 10), 'short:10', false];
        yield [new IgnoredViolationId(endLine: 10, endLinePosition: 10), 'short:9', true];

        yield [new IgnoredViolationId(startLine: 10), 'short::10', false];
        yield [new IgnoredViolationId(startLinePosition: 10), 'short::10', true];
        yield [new IgnoredViolationId(startLinePosition: 10), 'short::9', false];
        yield [new IgnoredViolationId(startLine: 10, startLinePosition: 10), 'short::10', false];
        yield [new IgnoredViolationId(endLine: 10), 'short::10', false];
        yield [new IgnoredViolationId(endLinePosition: 10), 'short::10', true];
        yield [new IgnoredViolationId(endLinePosition: 10), 'short::11', false];
        yield [new IgnoredViolationId(endLine: 10, endLinePosition: 10), 'short:10', false];
    }
}
