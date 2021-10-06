<?php

namespace TwigCsFixer\File;

use Symfony\Component\Finder\Finder as BaseFinder;

/**
 * Default configuration of Finder.
 */
final class Finder extends BaseFinder
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->files()
            ->name('*.twig')
            ->exclude('node_modules')
            ->exclude('vendor');
    }
}
