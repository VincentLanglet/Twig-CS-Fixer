<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/.phpstan',
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/.php-cs-fixer.php',
        __DIR__.'/rector.php',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
    ]);
    $rectorConfig->rules([
        PrivatizeFinalClassMethodRector::class,
        PrivatizeFinalClassPropertyRector::class,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
