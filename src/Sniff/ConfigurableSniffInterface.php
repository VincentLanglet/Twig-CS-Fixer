<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

interface ConfigurableSniffInterface extends SniffInterface
{
    /**
     * @return array<mixed>
     */
    public function getConfiguration(): array;
}
