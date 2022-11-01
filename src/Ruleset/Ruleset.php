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
     * @return $this
     */
    public function addSniff(SniffInterface $sniff): self
    {
        $this->sniffs[\get_class($sniff)] = $sniff;

        return $this;
    }

    /**
     * @param class-string<SniffInterface> $class
     *
     * @return $this
     */
    public function removeSniff(string $class): self
    {
        unset($this->sniffs[$class]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addStandard(StandardInterface $standard): self
    {
        foreach ($standard->getSniffs() as $sniff) {
            $this->addSniff($sniff);
        }

        return $this;
    }

    public function equals(self $ruleset): bool
    {
        $keys1 = array_keys($this->getSniffs());
        $keys2 = array_keys($ruleset->getSniffs());
        sort($keys1);
        sort($keys2);

        return $keys1 === $keys2;
    }
}
