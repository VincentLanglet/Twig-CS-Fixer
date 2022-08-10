<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset;

use TwigCsFixer\Sniff\SniffInterface;
use TwigCsFixer\Standard\StandardInterface;

use function get_class;

/**
 * Set of rules to be used by TwigCsFixer and contains all sniffs.
 */
final class Ruleset
{
    /**
     * @var array<class-string<SniffInterface>, SniffInterface>
     */
    private array $sniffs = [];

    /**
     * @return array<class-string<SniffInterface>, SniffInterface>
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
     * @param class-string<SniffInterface> $class
     *
     * @return $this
     */
    public function removeSniff(string $class): Ruleset
    {
        unset($this->sniffs[$class]);

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
