<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\Rules\DelimiterSpacingRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

$ruleset = new Ruleset();
$ruleset->addStandard(new Generic());
$ruleset->removeRule(DelimiterSpacingRule::class);

$config = new Config('Custom');
$config->setRuleset($ruleset);

return $config;
