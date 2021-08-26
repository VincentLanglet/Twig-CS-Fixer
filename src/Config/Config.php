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
    const FINDER_TWIGCS = 'twigcs';
    const FINDER_SYMFONY = 'symfony';

    /**
     * @var string
     */
    private $name;

    /**
     * @var Ruleset
     */
    private $ruleset;

    /**
     * @var string
     *
     * The finder class to use.
     */
    private $finder;

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
        $this->finder = 'symfony';
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
     * @return string
     */
    public function getFinder(): string
    {
        return $this->finder;
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
