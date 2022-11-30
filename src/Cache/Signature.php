<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Sniff\ConfigurableSniffInterface;

final class Signature
{
    /**
     * @param array<mixed> $sniffs
     */
    public function __construct(
        private string $phpVersion,
        private string $fixerVersion,
        private array $sniffs
    ) {
    }

    public static function fromRuleset(
        string $phpVersion,
        string $fixerVersion,
        Ruleset $ruleset,
    ): self {
        $sniffs = [];
        foreach ($ruleset->getSniffs() as $sniff) {
            $sniffs[$sniff::class] = $sniff instanceof ConfigurableSniffInterface
                ? $sniff->getConfiguration()
                : null;
        }

        return new self($phpVersion, $fixerVersion, $sniffs);
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
    public function getSniffs(): array
    {
        return $this->sniffs;
    }

    public function equals(self $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->sniffs === $signature->getSniffs();
    }
}
