<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Fixtures;

use TwigCsFixer\Rules\AbstractRule;

class FakeRule extends AbstractRule
{
    public function process(int $tokenPosition, array $tokens): void
    {
    }
}
