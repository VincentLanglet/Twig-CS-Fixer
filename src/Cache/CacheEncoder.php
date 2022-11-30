<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use InvalidArgumentException;
use JsonException;
use TwigCsFixer\Exception\CannotJsonEncodeException;
use Webmozart\Assert\Assert;

final class CacheEncoder
{
    /**
     * @throws InvalidArgumentException
     */
    public static function fromJson(string $json): Cache
    {
        try {
            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(sprintf(
                'Value needs to be a valid JSON string, got "%s", error: "%s".',
                $json,
                $e->getMessage()
            ));
        }

        Assert::isArray($data);

        Assert::keyExists($data, 'php_version');
        Assert::string($data['php_version']);
        Assert::keyExists($data, 'fixer_version');
        Assert::string($data['fixer_version']);
        Assert::keyExists($data, 'sniffs');
        Assert::isArray($data['sniffs']);

        $signature = new Signature(
            $data['php_version'],
            $data['fixer_version'],
            $data['sniffs'],
        );

        $cache = new Cache($signature);

        Assert::keyExists($data, 'hashes');
        Assert::isArray($data['hashes']);
        foreach ($data['hashes'] as $file => $hash) {
            Assert::string($file);
            Assert::string($hash);

            $cache->set($file, $hash);
        }

        return $cache;
    }

    /**
     * @throws CannotJsonEncodeException
     */
    public static function toJson(Cache $cache): string
    {
        $signature = $cache->getSignature();

        try {
            return json_encode([
                'php_version'   => $signature->getPhpVersion(),
                'fixer_version' => $signature->getFixerVersion(),
                'sniffs'        => $signature->getSniffs(),
                'hashes'        => $cache->getHashes(),
            ], \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw CannotJsonEncodeException::because($exception);
        }
    }
}
