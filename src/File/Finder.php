<?php

declare(strict_types=1);

namespace TwigCsFixer\File;

use Symfony\Component\Finder\Finder as BaseFinder;

final class Finder extends BaseFinder
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->files()
            ->name('/\.twig$/')
            ->exclude('node_modules')
            ->exclude('vendor');
    }
}
