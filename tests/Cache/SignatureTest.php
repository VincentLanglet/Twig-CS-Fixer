<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Signature;

class SignatureTest extends TestCase
{
    public function testSignature(): void
    {
        $signature = new Signature('8.0', '1', '{"TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff":null}');

        static::assertSame('8.0', $signature->getPhpVersion());
        static::assertSame('1', $signature->getFixerVersion());
        static::assertSame('{"TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff":null}', $signature->getRuleset());
    }

    /**
     * @dataProvider equalsDataProvider
     */
    public function testEquals(Signature $signature1, Signature $signature2, bool $expected): void
    {
        static::assertSame($expected, $signature1->equals($signature2));
        static::assertSame($expected, $signature2->equals($signature1));
    }

    /**
     * @return iterable<array-key, array{Signature, Signature, bool}>
     */
    public function equalsDataProvider(): iterable
    {
        $signature1 = new Signature('8.0', '1', '{"TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff":null}');
        $signature2 = new Signature('8.0', '1', '{"TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff":null}');
        $signature3 = new Signature('8.1', '1', '{"TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff":null}');
        $signature4 = new Signature('8.0', '2', '{"TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff":null}');
        $signature5 = new Signature('8.0', '1', '');

        yield [$signature1, $signature1, true];
        yield [$signature1, $signature2, true];
        yield [$signature1, $signature3, false];
        yield [$signature1, $signature4, false];
        yield [$signature1, $signature5, false];
    }
}
