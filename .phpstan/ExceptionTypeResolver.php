<?php

declare(strict_types=1);

namespace TwigCsFixer\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver;
use PHPStan\Rules\Exceptions\ExceptionTypeResolver as PHPStanExceptionTypeResolver;

final class ExceptionTypeResolver implements PHPStanExceptionTypeResolver
{
    public function __construct(private DefaultExceptionTypeResolver $defaultExceptionTypeResolver)
    {
    }

    public function isCheckedException(string $className, Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (null !== $namespace && str_starts_with($namespace, 'TwigCsFixer\Tests')) {
            return false;
        }

        return $this->defaultExceptionTypeResolver->isCheckedException($className, $scope);
    }
}
