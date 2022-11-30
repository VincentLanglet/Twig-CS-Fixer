<?php

declare(strict_types=1);

namespace TwigCsFixer\Exception;

use JsonException;
use Throwable;
use UnexpectedValueException;

final class CannotJsonEncodeException extends UnexpectedValueException
{
    private function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function because(JsonException $exception): self
    {
        $error = sprintf('Cannot encode to JSON, error: "%s".', $exception->getMessage());
        if (\in_array($exception->getCode(), [\JSON_ERROR_UTF8, \JSON_ERROR_UTF16], true)) {
            $error .= ' If you have non-UTF8 or non-UTF16 chars in your signature,'
                .' consider enabling `ext-mbstring` or install `symfony/polyfill-mbstring`.';
        }

        return new self($error, $exception->getCode(), $exception);
    }
}
