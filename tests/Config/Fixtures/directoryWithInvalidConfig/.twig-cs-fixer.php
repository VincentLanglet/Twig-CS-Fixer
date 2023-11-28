<?php

declare(strict_types=1);

use TwigCsFixer\Rules\DelimiterSpacingRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

$ruleset = new Ruleset();
$ruleset->addStandard(new Generic());
$ruleset->removeRule(DelimiterSpacingRule::class);

return $ruleset;
