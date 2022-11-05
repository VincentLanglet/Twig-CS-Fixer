<?php

declare(strict_types=1);

use TwigCsFixer\Cache\Manager\NullCacheManager;
use TwigCsFixer\Config\Config;

$config = new Config('Custom');
$config->setCacheManager(new NullCacheManager());

return $config;
