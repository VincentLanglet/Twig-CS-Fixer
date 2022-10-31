<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use Symfony\Component\Filesystem\Exception\IOException;
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

        return CacheEncoder::fromJson($content);
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

        $bytesWritten = @file_put_contents($this->file, CacheEncoder::toJson($cache));

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
}
