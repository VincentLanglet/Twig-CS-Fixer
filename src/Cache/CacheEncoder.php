<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use InvalidArgumentException;
use JsonException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\SniffInterface;
use UnexpectedValueException;
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

        Assert::keyExists($data, 'sniffs');
        Assert::isArray($data['sniffs']);

        $ruleSet = new RuleSet();
        foreach ($data['sniffs'] as $sniffName) {
            Assert::string($sniffName);
            Assert::classExists($sniffName);
            Assert::implementsInterface($sniffName, SniffInterface::class);

            $ruleSet->addSniff(new $sniffName());
        }

        Assert::keyExists($data, 'php_version');
        Assert::string($data['php_version']);
        Assert::keyExists($data, 'fixer_version');
        Assert::string($data['fixer_version']);

        $signature = new Signature(
            $data['php_version'],
            $data['fixer_version'],
            $ruleSet,
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
     * @throws UnexpectedValueException
     */
    public static function toJson(Cache $cache): string
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
                $error .= ' If you have non-UTF8 or non-UTF16 chars in your signature,'
                    .' consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.';
            }

            throw new UnexpectedValueException($error);
        }
    }
}
