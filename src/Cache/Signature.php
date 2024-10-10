<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Ruleset\Ruleset;

final class Signature
{
    /**
     * @param array<mixed> $rules
     */
    public function __construct(
        private string $phpVersion,
        private string $fixerVersion,
        private array $rules,
    ) {
    }

    public static function fromRuleset(
        string $phpVersion,
        string $fixerVersion,
        Ruleset $ruleset,
    ): self {
        $rules = [];
        foreach ($ruleset->getRules() as $rule) {
            $rules[$rule::class] = $rule instanceof ConfigurableRuleInterface
                ? $rule->getConfiguration()
                : null;
        }

        return new self($phpVersion, $fixerVersion, $rules);
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getFixerVersion(): string
    {
        return $this->fixerVersion;
    }

    /**
     * @return array<mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function equals(self $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->rules === $signature->getRules();
    }
}
