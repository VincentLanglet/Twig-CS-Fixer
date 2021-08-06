<?php

use TwigCsFixer\Config\Config;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Standard\Generic;

$ruleset = new Ruleset();
$ruleset->addStandard(new Generic());
$ruleset->removeSniff(DelimiterSpacingSniff::class);

$config = new Config('Custom');
$config->setRuleset($ruleset);

return $config;
