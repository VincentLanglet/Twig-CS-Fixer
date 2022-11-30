<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

final class Signature
{
    public function __construct(
        private string $phpVersion,
        private string $fixerVersion,
        private string $ruleset
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

    public function getRuleset(): string
    {
        return $this->ruleset;
    }

    public function equals(self $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->ruleset === $signature->getRuleset();
    }
}
