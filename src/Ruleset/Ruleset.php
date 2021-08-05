<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset;

use TwigCsFixer\Sniff\SniffInterface;
use TwigCsFixer\Standard\StandardInterface;

/**
 * Set of rules to be used by TwigCsFixer and contains all sniffs.
 */
final class Ruleset
{
    /**
     * @var SniffInterface[]
     */
    private $sniffs = [];

    /**
     * @return SniffInterface[]
     */
    public function getSniffs(): array
    {
        return $this->sniffs;
    }

    /**
     * @param SniffInterface $sniff
     *
     * @return $this
     */
    public function addSniff(SniffInterface $sniff): Ruleset
    {
        $this->sniffs[get_class($sniff)] = $sniff;

        return $this;
    }

    /**
     * @param SniffInterface $sniff
     *
     * @return $this
     */
    public function removeSniff(SniffInterface $sniff): Ruleset
    {
        unset($this->sniffs[get_class($sniff)]);

        return $this;
    }

    /**
     * @param StandardInterface $standard
     *
     * @return $this
     */
    public function addStandard(StandardInterface $standard): Ruleset
    {
        foreach ($standard->getSniffs() as $sniff) {
            $this->addSniff($sniff);
        }

        return $this;
    }
}
