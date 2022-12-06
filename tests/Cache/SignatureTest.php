<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\ConfigurableSniffInterface;
use TwigCsFixer\Sniff\OperatorSpacingSniff;
use TwigCsFixer\Sniff\SniffInterface;

final class SignatureTest extends TestCase
{
    public function testSignature(): void
    {
        $signature = new Signature('8.0', '1', [OperatorSpacingSniff::class => null]);

        static::assertSame('8.0', $signature->getPhpVersion());
        static::assertSame('1', $signature->getFixerVersion());
        static::assertSame([OperatorSpacingSniff::class => null], $signature->getSniffs());
    }

    public function testSignatureFromRuleset(): void
    {
        $ruleset = new Ruleset();

        $sniff = $this->createStub(SniffInterface::class);
        $ruleset->addSniff($sniff);

        $configurableSniff = $this->createStub(ConfigurableSniffInterface::class);
        $configurableSniff->method('getConfiguration')->willReturn(['a' => 1]);
        $ruleset->addSniff($configurableSniff);

        $signature = Signature::fromRuleset('8.0', '1', $ruleset);

        static::assertSame('8.0', $signature->getPhpVersion());
        static::assertSame('1', $signature->getFixerVersion());
        static::assertSame(
            [
                $sniff::class             => null,
                $configurableSniff::class => ['a' => 1],
            ],
            $signature->getSniffs()
        );
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
    public static function equalsDataProvider(): iterable
    {
        $signature1 = new Signature('8.0', '1', [OperatorSpacingSniff::class => null]);
        $signature2 = new Signature('8.0', '1', [OperatorSpacingSniff::class => null]);
        $signature3 = new Signature('8.1', '1', [OperatorSpacingSniff::class => null]);
        $signature4 = new Signature('8.0', '2', [OperatorSpacingSniff::class => null]);
        $signature5 = new Signature('8.0', '1', []);

        yield [$signature1, $signature1, true];
        yield [$signature1, $signature2, true];
        yield [$signature1, $signature3, false];
        yield [$signature1, $signature4, false];
        yield [$signature1, $signature5, false];
    }
}
