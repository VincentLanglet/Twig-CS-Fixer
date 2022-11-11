<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder as TwigCsFinder;

$finder = new TwigCsFinder();
$finder->in(__DIR__);

$config = new Config('Custom');
$config->setFinder($finder);

return $config;
