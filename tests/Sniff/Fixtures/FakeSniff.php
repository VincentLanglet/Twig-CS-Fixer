<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\Fixtures;

use TwigCsFixer\Sniff\AbstractSniff;

class FakeSniff extends AbstractSniff
{
    public function process(int $tokenPosition, array $tokens): void
    {
    }
}
