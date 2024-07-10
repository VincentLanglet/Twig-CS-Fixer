<?php

declare(strict_types=1);

namespace TwigCsFixer\Ruleset;

use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Rules\FixableRuleInterface;
use TwigCsFixer\Rules\Node\NodeRuleInterface;
use TwigCsFixer\Rules\RuleInterface;
use TwigCsFixer\Standard\StandardInterface;

/**
 * Set of rules to be used by TwigCsFixer and contains all rules.
 */
final class Ruleset
{
    /**
     * @var array<string, RuleInterface|NodeRuleInterface>
     */
    private array $rules = [];

    private bool $allowNonFixableRules = true;

    public function allowNonFixableRules(bool $allowNonFixableRules = true): self
    {
        $this->allowNonFixableRules = $allowNonFixableRules;

        return $this;
    }

    /**
     * @return list<RuleInterface|NodeRuleInterface>
     */
    public function getRules(): array
    {
        if (!$this->allowNonFixableRules) {
            return array_values(array_filter(
                $this->rules,
                static fn ($rule): bool => $rule instanceof FixableRuleInterface,
            ));
        }

        return array_values($this->rules);
    }

    /**
     * @return $this
     */
    public function addRule(RuleInterface|NodeRuleInterface $rule): self
    {
        $config = $rule instanceof ConfigurableRuleInterface
            ? $rule->getConfiguration()
            : null;
        $key = $rule::class.md5(serialize($config));

        $this->rules[$key] = $rule;

        return $this;
    }

    /**
     * @return $this
     */
    public function overrideRule(RuleInterface|NodeRuleInterface $rule): self
    {
        $this->removeRule($rule::class);
        $this->addRule($rule);

        return $this;
    }

    /**
     * @param class-string<RuleInterface|NodeRuleInterface> $class
     *
     * @return $this
     */
    public function removeRule(string $class): self
    {
        foreach ($this->rules as $key => $rule) {
            if ($rule::class === $class) {
                unset($this->rules[$key]);
            }
        }

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

    /**
     * @return $this
     */
    public function overrideStandard(StandardInterface $standard): self
    {
        foreach ($standard->getRules() as $rule) {
            $this->overrideRule($rule);
        }

        return $this;
    }
}
