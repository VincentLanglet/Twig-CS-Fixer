<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;
use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;

return [
    'exclude-namespaces' => ['TwigCsFixer'],
    'expose-classes' => [
        Finder::class,
        ExtensionInterface::class,
        NodeVisitorInterface::class,
        TokenParserInterface::class,
    ],
];
