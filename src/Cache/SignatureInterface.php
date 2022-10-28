<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

use TwigCsFixer\Ruleset\Ruleset;

interface SignatureInterface
{
    public function getPhpVersion(): string;

    public function getFixerVersion(): string;

    public function getRuleset(): Ruleset;

    public function equals(self $signature): bool;
}
