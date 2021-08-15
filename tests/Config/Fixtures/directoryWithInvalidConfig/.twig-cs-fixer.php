<?php

use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Standard\Generic;

$ruleset = new Ruleset();
$ruleset->addStandard(new Generic());
$ruleset->removeSniff(DelimiterSpacingSniff::class);

return $ruleset;
