<?php

declare(strict_types=1);

namespace TwigCsFixer\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver;
use PHPStan\Rules\Exceptions\ExceptionTypeResolver as PHPStanExceptionTypeResolver;

/**
 * Class ExceptionTypeResolver
 */
class ExceptionTypeResolver implements PHPStanExceptionTypeResolver
{
    /**
     * @var DefaultExceptionTypeResolver
     */
    private $defaultExceptionTypeResolver;

    public function __construct(DefaultExceptionTypeResolver $defaultExceptionTypeResolver)
    {
        $this->defaultExceptionTypeResolver = $defaultExceptionTypeResolver;
    }

    public function isCheckedException(string $className, Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (null !== $namespace && 0 === \strpos($namespace, 'TwigCsFixer\Tests')) {
            return false;
        }

        return $this->defaultExceptionTypeResolver->isCheckedException($className, $scope);
    }
}
