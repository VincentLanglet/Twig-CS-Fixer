<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\Tests\TestHelper;

$config = new Config('Custom');
$config->setCacheFile(TestHelper::getOsPath(__DIR__.'/.twig-cs-fixer.cache'));

return $config;
