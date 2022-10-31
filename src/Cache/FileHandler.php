<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\Filesystem\Exception\IOException;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\SniffInterface;
use UnexpectedValueException;

final class FileHandler implements FileHandlerInterface
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function read(): ?CacheInterface
    {
        if (!file_exists($this->file)) {
            return null;
        }

        $content = file_get_contents($this->file);
        if (false === $content) {
            return null;
        }

        return $this->fromJson($content);
    }

    /**
     * @throws IOException
     * @throws UnexpectedValueException
     */
    public function write(CacheInterface $cache): void
    {
        if (file_exists($this->file)) {
            if (is_dir($this->file)) {
                throw new IOException(
                    sprintf('Cannot write cache file "%s" as the location exists as directory.', $this->file),
                    0,
                    null,
                    $this->file
                );
            }

            if (!is_writable($this->file)) {
                throw new IOException(
                    sprintf('Cannot write to file "%s" as it is not writable.', $this->file),
                    0,
                    null,
                    $this->file
                );
            }
        } else {
            $dir = \dirname($this->file);

            if (!is_dir($dir)) {
                throw new IOException(
                    sprintf('Directory of cache file "%s" does not exists.', $this->file),
                    0,
                    null,
                    $this->file
                );
            }

            @touch($this->file);
            @chmod($this->file, 0666);
        }

        $bytesWritten = @file_put_contents($this->file, $this->toJson($cache));

        if (false === $bytesWritten) {
            $error = error_get_last();

            throw new IOException(
                sprintf('Failed to write file "%s", "%s".', $this->file, $error['message'] ?? 'no reason available'),
                0,
                null,
                $this->file
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function fromJson(string $json): Cache
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

        $missingKeys = array_diff_key(array_flip($requiredKeys), $data);

        if (\count($missingKeys) > 0) {
            throw new InvalidArgumentException(sprintf(
                'JSON data is missing keys "%s"',
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
            throw new InvalidArgumentException('PHP version must be a string.');
        }

        if (!\is_string($data['fixer_version'])) {
            throw new InvalidArgumentException('PHP version must be a string.');
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
    private function toJson(CacheInterface $cache): string
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
