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
 * @see https://symfony.com/doc/current/best_practices.html#templates
 * @see https://symfony.com/bundles/ux-twig-component/current/index.html
 * @see https://symfony.com/bundles/ux-turbo/current/index.html#broadcast-doctrine-entities-update
 */
final class Symfony implements StandardInterface
{
    public function getRules(): array
    {
        return [
            ...(new Twig())->getRules(),
            new FileNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['broadcast', 'bundles', 'components'], optionalPrefix: '_'),
            new FileNameRule(case: DirectoryNameRule::PASCAL_CASE, baseDirectory: 'templates/broadcast'),
            new FileNameRule(case: DirectoryNameRule::PASCAL_CASE, baseDirectory: 'templates/components'),
            new DirectoryNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['bundles', 'components']),
            new DirectoryNameRule(case: DirectoryNameRule::PASCAL_CASE, baseDirectory: 'templates/components'),
            new FileExtensionRule(),
        ];
    }
}
