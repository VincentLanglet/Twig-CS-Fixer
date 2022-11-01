<?php

use TwigCsFixer\Cache\NullCacheManager;
use TwigCsFixer\Config\Config;

$config = new Config('Custom');
$config->setCacheManager(new NullCacheManager());

return $config;
