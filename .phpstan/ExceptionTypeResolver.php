<?php

namespace TwigCsFixer\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Exceptions\DefaultExceptionTypeResolver;
use PHPStan\Rules\Exceptions\ExceptionTypeResolver as PHPStanExceptionTypeResolver;

use function strpos;

/**
 * Class ExceptionTypeResolver
 */
class ExceptionTypeResolver implements PHPStanExceptionTypeResolver
{
    /**
     * @var DefaultExceptionTypeResolver
     */
    private $defaultExceptionTypeResolver;

    /**
     * @param DefaultExceptionTypeResolver $defaultExceptionTypeResolver
     *
     * @return void
     */
    public function __construct(DefaultExceptionTypeResolver $defaultExceptionTypeResolver)
    {
        $this->defaultExceptionTypeResolver = $defaultExceptionTypeResolver;
    }

    /**
     * @param string $className
     * @param Scope  $scope
     *
     * @return bool
     */
    public function isCheckedException(string $className, Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if (null !== $namespace && 0 === strpos($namespace, 'TwigCsFixer\Tests')) {
            return false;
        }

        return $this->defaultExceptionTypeResolver->isCheckedException($className, $scope);
    }
}
