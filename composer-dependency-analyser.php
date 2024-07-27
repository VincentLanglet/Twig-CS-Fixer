<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    ->addPathToScan(__DIR__.'/bin/twig-cs-fixer', isDev: false)
    ->ignoreErrorsOnPackageAndPath(
        'symfony/twig-bridge',
        __DIR__.'/src/Environment/StubbedEnvironment.php',
        [ErrorType::DEV_DEPENDENCY_IN_PROD]
    )
    ->ignoreErrorsOnPackageAndPath(
        'symfony/ux-twig-component',
        __DIR__.'/src/Environment/StubbedEnvironment.php',
        [ErrorType::DEV_DEPENDENCY_IN_PROD]
    )
    ->ignoreErrorsOnPackageAndPath(
        'twig/cache-extra',
        __DIR__.'/src/Environment/StubbedEnvironment.php',
        [ErrorType::DEV_DEPENDENCY_IN_PROD]
    );
