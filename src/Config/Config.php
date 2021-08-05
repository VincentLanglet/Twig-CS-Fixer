<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

/**
 * Main entry point to config the TwigCsFixer.
 */
class Config
{
    /**
     * @var Ruleset
     */
    private $ruleset;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->ruleset = new Ruleset();
        $this->ruleset->addStandard(new Generic());
    }

    /**
     * @return Ruleset
     */
    public function getRuleset(): Ruleset
    {
        return $this->ruleset;
    }

    /**
     * @param Ruleset $ruleset
     *
     * @return $this
     */
    public function setRuleset(Ruleset $ruleset): self
    {
        $this->ruleset = $ruleset;

        return $this;
    }
}
