<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset;

use TwigCsFixer\Rules\RuleInterface;
use TwigCsFixer\Standard\StandardInterface;

/**
 * Set of rules to be used by TwigCsFixer and contains all rules.
 */
final class Ruleset
{
    /**
     * @var array<class-string<RuleInterface>, RuleInterface>
     */
    private array $rules = [];

    /**
     * @return array<class-string<RuleInterface>, RuleInterface>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return $this
     */
    public function addRule(RuleInterface $rule): self
    {
        $this->rules[$rule::class] = $rule;

        return $this;
    }

    /**
     * @param class-string<RuleInterface> $class
     *
     * @return $this
     */
    public function removeRule(string $class): self
    {
        unset($this->rules[$class]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addStandard(StandardInterface $standard): self
    {
        foreach ($standard->getRules() as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }
}
