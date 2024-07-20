<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\Source;
use Twig\TokenParser\TokenParserInterface;

return [
    'exclude-namespaces' => ['TwigCsFixer'],
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'expose-namespaces' => ['Twig\Node'],
    'expose-classes' => [
        OutputInterface::class,
        Finder::class,
        Environment::class,
        ExtensionInterface::class,
        NodeVisitorInterface::class,
        Source::class,
        TokenParserInterface::class,
    ],
];
