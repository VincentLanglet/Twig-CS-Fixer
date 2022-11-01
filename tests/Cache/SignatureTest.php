<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\OperatorSpacingSniff;

class SignatureTest extends TestCase
{
    public function testSignature(): void
    {
        $ruleSet = new Ruleset();
        $signature = new Signature('8.0', '1', $ruleSet);

        static::assertSame('8.0', $signature->getPhpVersion());
        static::assertSame('1', $signature->getFixerVersion());
        static::assertSame($ruleSet, $signature->getRuleset());
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
        $ruleSet1 = new Ruleset();
        $ruleSet1->addSniff(new OperatorSpacingSniff());
        $ruleSet2 = new Ruleset();
        $ruleSet3 = new Ruleset();
        $ruleSet3->addSniff(new OperatorSpacingSniff());

        $signature1 = new Signature('8.0', '1', $ruleSet1);
        $signature2 = new Signature('8.1', '1', $ruleSet1);
        $signature3 = new Signature('8.0', '2', $ruleSet1);
        $signature4 = new Signature('8.0', '1', $ruleSet2);
        $signature5 = new Signature('8.0', '1', $ruleSet3);

        yield [$signature1, $signature1, true];
        yield [$signature1, $signature2, false];
        yield [$signature1, $signature3, false];
        yield [$signature1, $signature4, false];
        yield [$signature1, $signature5, true];
    }
}
