<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Rules\File\DirectoryNameRule;
use TwigCsFixer\Rules\File\FileExtensionRule;
use TwigCsFixer\Rules\File\FileNameRule;

/**
 * Standard from Symfony.
 *
 * @see https://twig.symfony.com/doc/3.x/coding_standards.html
 * @see https://symfony.com/doc/current/templates.html#template-naming
 */
final class Symfony implements StandardInterface
{
    public function getRules(): array
    {
        return [
            ...(new Twig())->getRules(),
            new FileNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['bundles']),
            new DirectoryNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['bundles']),
            new FileExtensionRule(),
        ];
    }
}
