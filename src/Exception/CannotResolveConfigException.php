<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

use TwigCsFixer\Config\Config;

final class CannotResolveConfigException extends \RuntimeException
{
    private function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fileNotFound(string $path): self
    {
        return new self(\sprintf('Cannot find the config file "%s".', $path));
    }

    public static function fileMustReturnConfig(string $path): self
    {
        return new self(\sprintf('The config file "%s" must return a "%s" object.', $path, Config::class));
    }
}
