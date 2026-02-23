<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/.phpstan',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/.php-cs-fixer.php',
        __DIR__.'/rector.php',
        __DIR__.'/scoper.inc.php',
    ])
    ->withPhpSets()
    ->withRules([
        PrivatizeFinalClassMethodRector::class,
        PrivatizeFinalClassPropertyRector::class,
        StaticArrowFunctionRector::class,
        StaticClosureRector::class,
    ])
    ->withSkip([
        // @see https://github.com/rectorphp/rector/issues/9672
        NewInInitializerRector::class,
    ])
    ->withImportNames(importShortClasses: false);
