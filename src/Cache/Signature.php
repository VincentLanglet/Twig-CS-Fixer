<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use TwigCsFixer\Ruleset\Ruleset;

final class Signature
{
    public function __construct(
        private string $phpVersion,
        private string $fixerVersion,
        private Ruleset $ruleset
    ) {
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getFixerVersion(): string
    {
        return $this->fixerVersion;
    }

    public function getRuleset(): Ruleset
    {
        return $this->ruleset;
    }

    public function equals(self $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->ruleset->equals($signature->getRuleset());
    }
}
