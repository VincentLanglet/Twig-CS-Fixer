<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use InvalidArgumentException;
use TwigCsFixer\RuleSet\RuleSet;
use UnexpectedValueException;

/**
 * @internal
 */
final class Cache implements CacheInterface
{
    /**
     * @var SignatureInterface
     */
    private $signature;

    /**
     * @var array<string, int>
     */
    private $hashes = [];

    public function __construct(SignatureInterface $signature)
    {
        $this->signature = $signature;
    }

    public function getSignature(): SignatureInterface
    {
        return $this->signature;
    }

    public function has(string $file): bool
    {
        return \array_key_exists($file, $this->hashes);
    }

    public function get(string $file): ?int
    {
        if (!$this->has($file)) {
            return null;
        }

        return $this->hashes[$file];
    }

    public function set(string $file, int $hash): void
    {
        $this->hashes[$file] = $hash;
    }

    public function clear(string $file): void
    {
        unset($this->hashes[$file]);
    }

    public function toJson(): string
    {
        $json = json_encode([
            'php'     => $this->getSignature()->getPhpVersion(),
            'version' => $this->getSignature()->getFixerVersion(),
            'sniffs'  => array_keys($this->getSignature()->getRuleset()->getSniffs()),
            'hashes'  => $this->hashes,
        ]);

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new UnexpectedValueException(sprintf(
                'Cannot encode cache signature to JSON, error: "%s". If you have non-UTF8 chars in your signature, like in license for `header_comment`, consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.',
                json_last_error_msg()
            ));
        }

        return $json;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (null === $data && \JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Value needs to be a valid JSON string, got "%s", error: "%s".',
                $json,
                json_last_error_msg()
            ));
        }

        $requiredKeys = [
            'php',
            'version',
            'sniffs',
            'hashes',
        ];

        $missingKeys = array_diff_key(array_flip($requiredKeys), $data);

        if (\count($missingKeys) > 0) {
            throw new InvalidArgumentException(sprintf(
                'JSON data is missing keys "%s"',
                implode('", "', $missingKeys)
            ));
        }

        $ruleSet = new RuleSet();
        foreach ($data['sniffs'] as $sniff) {
            $ruleSet->addSniff(new $sniff());
        }

        $signature = new Signature(
            $data['php'],
            $data['version'],
            $ruleSet,
        );

        $cache = new self($signature);

        $cache->hashes = $data['hashes'];

        return $cache;
    }
}
