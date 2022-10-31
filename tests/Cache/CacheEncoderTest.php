<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Cache;
use TwigCsFixer\Cache\CacheEncoder;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\OperatorSpacingSniff;
use TwigCsFixer\Sniff\PunctuationSpacingSniff;

class CacheEncoderTest extends TestCase
{
    /**
     * @dataProvider fromJsonFailureDataProvider
     */
    public function testFromJsonFailure(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        CacheEncoder::fromJson($input);
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public function fromJsonFailureDataProvider(): iterable
    {
        yield [''];
        yield ['null'];
        yield ['{}'];
        yield ['{"php_version":12,"fixer_version":13,"sniffs":[],"hashes":"yes"}'];
        yield ['{"php_version":12,"fixer_version":13,"sniffs":[],"hashes":["yes"]}'];
        yield ['{"php_version":"7.4","fixer_version":13,"sniffs":[],"hashes":["yes"]}'];
        yield ['{"php_version":"7.4","fixer_version":"1.3","sniffs":[],"hashes":["yes"]}'];
        yield ['{"php_version":"7.4","fixer_version":"1.3","sniffs":["TwigCsFixer\\\\Sniff\\\\UnknownSniff"],"hashes":{"folder/file.twig":"bnmdsa678dsa","anotherfolder/anotherfile.twig":"123bnmdsa678dsa"}}'];
    }

    public function testFromJsonSuccess(): void
    {
        $cache = CacheEncoder::fromJson('{"php_version":"7.4","fixer_version":"1.3","sniffs":["TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff","TwigCsFixer\\\\Sniff\\\\PunctuationSpacingSniff"],"hashes":{"folder/file.twig":"bnmdsa678dsa","anotherfolder/anotherfile.twig":"123bnmdsa678dsa"}}');

        $signature = $cache->getSignature();
        self::assertEquals('7.4', $signature->getPhpVersion());
        self::assertEquals('1.3', $signature->getFixerVersion());
        self::assertCount(2, $cache->getHashes());
        self::assertSame(
            ['folder/file.twig' => 'bnmdsa678dsa', 'anotherfolder/anotherfile.twig' => '123bnmdsa678dsa'],
            $cache->getHashes()
        );

        $sniffs = $signature->getRuleset()->getSniffs();
        self::assertCount(2, $sniffs);
        self::assertSame(
            [OperatorSpacingSniff::class, PunctuationSpacingSniff::class],
            array_keys($sniffs)
        );
    }

    public function testToJsonSuccess(): void
    {
        $ruleset = new Ruleset();
        $ruleset->addSniff(new OperatorSpacingSniff());

        $signature = new Signature('7.4', '1.3', $ruleset);
        $cache = new Cache($signature);

        self::assertSame(
            '{"php_version":"7.4","fixer_version":"1.3","sniffs":["TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff"],"hashes":[]}',
            CacheEncoder::toJson($cache)
        );
    }
}
