<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Ruleset\Ruleset;

/**
 * This file was copied (and slightly modified) from PHP CS Fixer:
 * - https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/src/Cache/Signature.php
 * - (c) Fabien Potencier <fabien@symfony.com>, Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * - For the full copyright and license information, please see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.13.0/LICENSE
 */
final class Signature
{
    /**
     * @param array<mixed> $rules
     */
    public function __construct(
        private readonly string $phpVersion,
        private readonly string $fixerVersion,
        private readonly array $rules,
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
