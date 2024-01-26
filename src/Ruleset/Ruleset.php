<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset;

use TwigCsFixer\Rules\FixableRuleInterface;
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

    private bool $allowNonFixableRules = true;

    public function allowNonFixableRules(bool $allowNonFixableRules = true): self
    {
        $this->allowNonFixableRules = $allowNonFixableRules;

        return $this;
    }

    /**
     * @return array<class-string<RuleInterface>, RuleInterface>
     */
    public function getRules(): array
    {
        if (!$this->allowNonFixableRules) {
            return array_filter(
                $this->rules,
                static fn (RuleInterface $rule): bool => $rule instanceof FixableRuleInterface,
            );
        }

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
