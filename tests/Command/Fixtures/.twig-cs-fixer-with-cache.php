<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\Ruleset\Ruleset;

$config = new Config();
$config->setRuleset(new Ruleset());
$config->setCacheFile(__DIR__.'/.twig-cs-fixer.cache');

return $config;
