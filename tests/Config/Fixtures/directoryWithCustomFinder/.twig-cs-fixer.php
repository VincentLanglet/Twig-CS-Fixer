<?php

use TwigCsFixer\Config\Config;
use TwigCsFixer\File\Finder as TwigCsFinder;

$finder = new TwigCsFinder();
$finder->in('tests/Config/Fixtures/directoryWithCustomFinder');

$config = new Config('Custom');
$config->setFinder($finder);

return $config;
