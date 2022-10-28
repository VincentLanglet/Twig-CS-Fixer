<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use InvalidArgumentException;
use JsonException;
use LogicException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\SniffInterface;
use UnexpectedValueException;

final class Cache implements CacheInterface
{
    private SignatureInterface $signature;

    /**
     * @var array<string, int>
     */
    private array $hashes = [];

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

    /**
     * @throws LogicException
     */
    public function get(string $file): int
    {
        if (!$this->has($file)) {
            throw new LogicException('You should call the \'has\' method prior to calling \'get\'');
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

    /**
     * @throws UnexpectedValueException
     */
    public function toJson(): string
    {
        try {
            $json = json_encode([
                'php'     => $this->getSignature()->getPhpVersion(),
                'version' => $this->getSignature()->getFixerVersion(),
                'sniffs'  => array_keys($this->getSignature()->getRuleset()->getSniffs()),
                'hashes'  => $this->hashes,
            ], \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $error = sprintf('Cannot encode cache signature to JSON, error: "%s".', $e->getMessage());
            if (\in_array($e->getCode(), [\JSON_ERROR_UTF8, \JSON_ERROR_UTF16], true)) {
                $error .= ' If you have non-UTF8 or non-UTF16 chars in your signature, like in license for `header_comment`, consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.';
            }
            throw new UnexpectedValueException($error);
        }

        return $json;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromJson(string $json): self
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
        if (\is_array($data['sniffs'])) {
            foreach ($data['sniffs'] as $sniffName) {
                $sniff = new $sniffName();
                if ($sniff instanceof SniffInterface) {
                    $ruleSet->addSniff($sniff);
                }
            }
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
