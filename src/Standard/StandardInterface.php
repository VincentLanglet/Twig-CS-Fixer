<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Sniff\SniffInterface;

interface StandardInterface
{
    /**
     * @return SniffInterface[]
     */
    public function getSniffs(): array;
}
