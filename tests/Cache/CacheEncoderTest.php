<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\CacheEncoder;

class CacheEncoderTest extends TestCase
{
    /** @dataProvider expectedExceptions */
    public function testGetsInvalidExceptionFromJson(string $input, string $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        CacheEncoder::fromJson($input);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function expectedExceptions(): array
    {
        return [
            ['', 'Value needs to be a valid JSON string, got "", error: "Syntax error".'],
            ['null', 'Value needs to decode to an array.'],
            ['{}', 'JSON data is missing keys "php_version", "fixer_version", "sniffs", "hashes".'],
            ['{"php_version":12,"fixer_version":13,"sniffs":[],"hashes":"yes"}', 'hashes must be an array.'],
            ['{"php_version":12,"fixer_version":13,"sniffs":[],"hashes":["yes"]}', 'php_version must be a string.'],
            ['{"php_version":"7.4","fixer_version":13,"sniffs":[],"hashes":["yes"]}', 'fixer_version must be a string.'],
            ['{"php_version":"7.4","fixer_version":"1.3","sniffs":[],"hashes":["yes"]}', 'Cache file and hash must be strings.'],
            ['{"php_version":"7.4","fixer_version":"1.3","sniffs":["TwigCsFixer\\\\Sniff\\\\UnknownSniff"],"hashes":{"folder/file.twig":"bnmdsa678dsa","anotherfolder/anotherfile.twig":"123bnmdsa678dsa"}}', 'Sniff class "TwigCsFixer\\Sniff\\UnknownSniff" does not exist.'],
        ];
    }

    public function testGetsValidCacheFromJson(): void
    {
        $cache = CacheEncoder::fromJson('{"php_version":"7.4","fixer_version":"1.3","sniffs":["TwigCsFixer\\\\Sniff\\\\OperatorSpacingSniff","TwigCsFixer\\\\Sniff\\\\PunctuationSpacingSniff"],"hashes":{"folder/file.twig":"bnmdsa678dsa","anotherfolder/anotherfile.twig":"123bnmdsa678dsa"}}');
        $signature = $cache->getSignature();
        self::assertEquals('7.4', $signature->getPhpVersion(), 'Invalid PHP version found.');
        self::assertEquals('1.3', $signature->getFixerVersion(), 'Invalid Twig CS fixer version found.');
        self::assertCount(2, $cache->getHashes(), 'Wrong hashes count.');
        self::assertEquals(['folder/file.twig' => 'bnmdsa678dsa', 'anotherfolder/anotherfile.twig' => '123bnmdsa678dsa'], $cache->getHashes(), 'Incorrect hashes found.');
        $sniffs = $signature->getRuleset()->getSniffs();
        self::assertCount(2, $sniffs, 'Wrong sniffs count.');
        self::assertEquals(['TwigCsFixer\Sniff\OperatorSpacingSniff', 'TwigCsFixer\Sniff\PunctuationSpacingSniff'], array_keys($sniffs), 'Invalid sniffs found.');
    }
}
