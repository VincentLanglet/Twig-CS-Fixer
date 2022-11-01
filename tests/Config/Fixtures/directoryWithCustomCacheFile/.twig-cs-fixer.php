<?php

use TwigCsFixer\Cache\NullCacheManager;
use TwigCsFixer\Config\Config;

$config = new Config('Custom');
$config->setCacheFile(__DIR__.\DIRECTORY_SEPARATOR.'.twig-cs-fixer.cache');

return $config;
