<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use TwigCsFixer\Ruleset\Ruleset;

final class Signature implements SignatureInterface
{
    private string $phpVersion;

    private string $fixerVersion;

    private Ruleset $ruleset;

    public function __construct(string $phpVersion, string $fixerVersion, Ruleset $ruleset)
    {
        $this->phpVersion = $phpVersion;
        $this->fixerVersion = $fixerVersion;
        $this->ruleset = $ruleset;
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

    public function equals(SignatureInterface $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->ruleset->equals($signature->getRuleset());
    }
}
