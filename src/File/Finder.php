<?php

namespace TwigCsFixer\File;

use Symfony\Component\Finder\Finder as BaseFinder;

class Finder extends BaseFinder
{
    public function __construct()
    {
        parent::__construct();

        // @todo How does this know what paths to use?
        $this
            ->in('./')
            ->files()
            ->name('*.twig')
            ->exclude('vendor');
        ;
    }
}
