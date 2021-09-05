<?php

use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder as TwigCsFinder;

$finder = new TwigCsFinder();
$finder->name('*.twig.dist');

$config = new Config('Custom');
$config->setFinder($finder);

return $config;
