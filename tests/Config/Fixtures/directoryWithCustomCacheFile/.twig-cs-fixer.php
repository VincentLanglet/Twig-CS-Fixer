<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;

$config = new Config('Custom');
$config->setCacheFile(__DIR__.'/.twig-cs-fixer.cache');

return $config;
