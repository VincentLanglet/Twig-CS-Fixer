<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

return [
    'exclude-namespaces' => ['TwigCsFixer'],
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'expose-namespaces' => ['/^Twig(?!\\\\Test(\\\\|$))/'],
    'expose-classes' => [
        Finder::class,
        OutputInterface::class,
    ],
];
