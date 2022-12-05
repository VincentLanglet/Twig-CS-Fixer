<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Ruleset\Ruleset;

interface FixerInterface
{
    /**
     * @throws CannotTokenizeException
     * @throws CannotFixFileException
     */
    public function fixFile(string $content, Ruleset $ruleset): string;
}
