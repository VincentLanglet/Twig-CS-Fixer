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
    private string $name;

    private Ruleset $ruleset;

    private Finder $finder;

    public function __construct(string $name = 'Default')
    {
        $this->name = $name;
        $this->ruleset = new Ruleset();
        $this->ruleset->addStandard(new Generic());
        $this->finder = new TwigCsFinder();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRuleset(): Ruleset
    {
        return $this->ruleset;
    }

    /**
     * @return $this
     */
    public function setRuleset(Ruleset $ruleset): self
    {
        $this->ruleset = $ruleset;

        return $this;
    }

    public function getFinder(): Finder
    {
        return $this->finder;
    }

    /**
     * @return $this
     */
    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }
}
