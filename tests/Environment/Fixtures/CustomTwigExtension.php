<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment\Fixtures;

use Twig\Extension\ExtensionInterface;

final class CustomTwigExtension implements ExtensionInterface
{
    public function getTokenParsers(): array
    {
        return [
            new CustomTokenParser(),
        ];
    }

    public function getNodeVisitors(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getTests(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function getOperators(): array
    {
        return [[], []];
    }
}
