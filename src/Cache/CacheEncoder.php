<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use Webmozart\Assert\Assert;

/**
 * This file was copied (and slightly modified) from PHP CS Fixer:
 * - https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/src/Cache/Cache.php
 * - (c) Fabien Potencier <fabien@symfony.com>, Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * - For the full copyright and license information, please see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/LICENSE
 */
final class CacheEncoder
{
    /**
     * @throws \InvalidArgumentException
     */
    public static function fromJson(string $json): Cache
    {
        try {
            $data = json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException(\sprintf(
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
        Assert::keyExists($data, 'rules');
        Assert::isArray($data['rules']);

        $signature = new Signature(
            $data['php_version'],
            $data['fixer_version'],
            $data['rules'],
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
     * @throws \JsonException
     */
    public static function toJson(Cache $cache): string
    {
        $signature = $cache->getSignature();

        return json_encode([
            'php_version' => $signature->getPhpVersion(),
            'fixer_version' => $signature->getFixerVersion(),
            'rules' => $signature->getRules(),
            'hashes' => $cache->getHashes(),
        ], \JSON_THROW_ON_ERROR);
    }
}
