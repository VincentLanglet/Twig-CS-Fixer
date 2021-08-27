<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Symfony\Component\Finder\Finder;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;
use TwigCsFixer\File\Finder as TwigCsFinder;

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
     * @var Finder
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
        $this->finder = new TwigCsFinder();
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
     * @return Finder
     */
    public function getFinder(): Finder
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

    /**
     * @param Finder $finder
     */
    public function setFinder(Finder $finder)
    {
        $this->finder = $finder;
    }
}
