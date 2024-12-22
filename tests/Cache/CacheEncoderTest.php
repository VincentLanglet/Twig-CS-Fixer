<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\CacheEncoder;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Rules\Operator\OperatorSpacingRule;

final class CacheEncoderTest extends TestCase
{
    /**
     * @dataProvider fromJsonFailureDataProvider
     */
    public function testFromJsonFailure(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CacheEncoder::fromJson($input);
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public static function fromJsonFailureDataProvider(): iterable
    {
        yield [''];
        yield ['null'];
        yield ['{}'];
        yield ['{"php_version":12,"fixer_version":13,"rules":[],"hashes":"yes"}'];
        yield ['{"php_version":12,"fixer_version":13,"rules":[],"hashes":["yes"]}'];
        yield ['{"php_version":"7.4","fixer_version":13,"rules":[],"hashes":["yes"]}'];
        yield ['{"php_version":"7.4","fixer_version":"1.3","rules":[],"hashes":["yes"]}'];
    }

    public function testFromJsonSuccess(): void
    {
        $cache = CacheEncoder::fromJson('{"php_version":"7.4","fixer_version":"1.3","rules":{"TwigCsFixer\\\\Rules\\\\Operator\\\\OperatorSpacingRule":null},"hashes":{"folder/file.twig":"bnmdsa678dsa","anotherfolder/anotherfile.twig":"123bnmdsa678dsa"}}');

        $signature = $cache->getSignature();
        static::assertSame('7.4', $signature->getPhpVersion());
        static::assertSame('1.3', $signature->getFixerVersion());
        static::assertCount(2, $cache->getHashes());
        static::assertSame(
            ['folder/file.twig' => 'bnmdsa678dsa', 'anotherfolder/anotherfile.twig' => '123bnmdsa678dsa'],
            $cache->getHashes()
        );
        static::assertSame(
            [OperatorSpacingRule::class => null],
            $signature->getRules()
        );
    }

    public function testToJsonSuccess(): void
    {
        $signature = new Signature('7.4', '1.3', [OperatorSpacingRule::class => null]);
        $cache = new Cache($signature);

        static::assertSame(
            '{"php_version":"7.4","fixer_version":"1.3","rules":{"TwigCsFixer\\\\Rules\\\\Operator\\\\OperatorSpacingRule":null},"hashes":[]}',
            CacheEncoder::toJson($cache)
        );
    }

    public function testToJsonError(): void
    {
        $signature = new Signature('7.4', "\xB1\x31", []);
        $cache = new Cache($signature);

        $this->expectException(\JsonException::class);
        CacheEncoder::toJson($cache);
    }
}
