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
     * @var string
     */
    private $name;

    /**
     * @var Ruleset
     */
    private $ruleset;

    /**
     * @param string $name
     *
     * @return void
     */
    public function __construct(string $name = 'Default')
    {
        $this->name = $name;
        $this->ruleset = new Ruleset();
        $this->ruleset->addStandard(new Generic());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
