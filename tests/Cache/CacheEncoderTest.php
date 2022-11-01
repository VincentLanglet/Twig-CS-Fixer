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
use UnexpectedValueException;

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
        static::assertEquals('7.4', $signature->getPhpVersion());
        static::assertEquals('1.3', $signature->getFixerVersion());
        static::assertCount(2, $cache->getHashes());
        static::assertSame(
            ['folder/file.twig' => 'bnmdsa678dsa', 'anotherfolder/anotherfile.twig' => '123bnmdsa678dsa'],
            $cache->getHashes()
        );

        $sniffs = $signature->getRuleset()->getSniffs();
        static::assertCount(2, $sniffs);
        static::assertSame(
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

        static::assertSame(
            '{"php_version":"7.4","fixer_version":"1.3","sniffs":["TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff"],"hashes":[]}',
            CacheEncoder::toJson($cache)
        );
    }

    public function testToJsonError(): void
    {
        $ruleset = new Ruleset();
        $signature = new Signature('7.4', "\xB1\x31", $ruleset);
        $cache = new Cache($signature);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Cannot encode cache signature to JSON, error:'
            .' "Malformed UTF-8 characters, possibly incorrectly encoded".'
            .' If you have non-UTF8 or non-UTF16 chars in your signature,'
            .' consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.'
        );
        CacheEncoder::toJson($cache);
    }
}
