<?php

declare(strict_types=1);

namespace TwigCsFixer\Cache;

interface DirectoryInterface
{
    public function getRelativePathTo(string $file): string;
}
