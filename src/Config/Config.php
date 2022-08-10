<?php

declare(strict_types=1);

namespace TwigCsFixer\Config;

use Symfony\Component\Finder\Finder;
use TwigCsFixer\File\Finder as TwigCsFinder;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Generic;

/**
 * Main entry point to config the TwigCsFixer.
 */
final class Config
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var Ruleset
     */
    private Ruleset $ruleset;

    /**
     * @var Finder
     */
    private Finder $finder;

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
     * @return Finder
     */
    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * @param Finder $finder
     *
     * @return $this
     */
    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }
}
