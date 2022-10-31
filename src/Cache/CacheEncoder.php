<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use InvalidArgumentException;
use JsonException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\SniffInterface;
use UnexpectedValueException;

class CacheEncoder
{
    /**
     * @throws InvalidArgumentException
     */
    public static function fromJson(string $json): Cache
    {
        try {
            $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

            if (!\is_array($data)) {
                throw new InvalidArgumentException('Value needs to decode to an array.');
            }
        } catch (JsonException $e) {
            throw new InvalidArgumentException(sprintf(
                'Value needs to be a valid JSON string, got "%s", error: "%s".',
                $json,
                $e->getMessage()
            ));
        }

        $requiredKeys = [
            'php_version',
            'fixer_version',
            'sniffs',
            'hashes',
        ];

        $missingKeys = array_diff($requiredKeys, array_keys($data));

        if (\count($missingKeys) > 0) {
            throw new InvalidArgumentException(sprintf(
                'JSON data is missing keys "%s".',
                implode('", "', $missingKeys)
            ));
        }

        if (!\is_array($data['hashes'])) {
            throw new InvalidArgumentException('hashes must be an array.');
        }

        if (!\is_array($data['sniffs'])) {
            throw new InvalidArgumentException('sniffs must be an array.');
        }

        $ruleSet = new RuleSet();
        foreach ($data['sniffs'] as $sniffOffset => $sniffName) {
            if (!\is_string($sniffName)) {
                throw new InvalidArgumentException(sprintf(
                    'Sniff #%d should be a string.',
                    $sniffOffset
                ));
            }
            if (!class_exists($sniffName)) {
                throw new InvalidArgumentException(sprintf(
                    'Sniff class "%s" does not exist.',
                    $sniffName
                ));
            }
            if (!is_a($sniffName, SniffInterface::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Sniff class "%s" should implement TwigCsFixer\Sniff\SniffInterface.',
                    $sniffName
                ));
            }
            $ruleSet->addSniff(new $sniffName());
        }
        if (!\is_string($data['php_version'])) {
            throw new InvalidArgumentException('php_version must be a string.');
        }

        if (!\is_string($data['fixer_version'])) {
            throw new InvalidArgumentException('fixer_version must be a string.');
        }

        $signature = new Signature(
            $data['php_version'],
            $data['fixer_version'],
            $ruleSet,
        );

        $cache = new Cache($signature);

        foreach ($data['hashes'] as $file => $hash) {
            if (!\is_string($file) || !\is_string($hash)) {
                throw new InvalidArgumentException('Cache file and hash must be strings.');
            }
            $cache->set($file, $hash);
        }

        return $cache;
    }

    /**
     * @throws UnexpectedValueException
     */
    public static function toJson(CacheInterface $cache): string
    {
        $signature = $cache->getSignature();
        try {
            return json_encode([
                'php_version'     => $signature->getPhpVersion(),
                'fixer_version'   => $signature->getFixerVersion(),
                'sniffs'          => array_keys($signature->getRuleset()->getSniffs()),
                'hashes'          => $cache->getHashes(),
            ], \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $error = sprintf('Cannot encode cache signature to JSON, error: "%s".', $e->getMessage());
            if (\in_array($e->getCode(), [\JSON_ERROR_UTF8, \JSON_ERROR_UTF16], true)) {
                $error .= ' If you have non-UTF8 or non-UTF16 chars in your signature, like in license for `header_comment`, consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.';
            }
            throw new UnexpectedValueException($error);
        }
    }
}
