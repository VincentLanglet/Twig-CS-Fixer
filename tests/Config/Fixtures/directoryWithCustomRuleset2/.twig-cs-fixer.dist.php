<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\TwigCsFixer;

$ruleset = new Ruleset();
$ruleset->addStandard(new TwigCsFixer());
$ruleset->removeRule(DelimiterSpacingRule::class);

$config = new Config('CustomDist');
$config->setRuleset($ruleset);

return $config;
