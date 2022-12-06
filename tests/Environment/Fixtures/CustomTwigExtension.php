<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Environment\Fixtures;

use Twig\ExpressionParser;
use Twig\Extension\ExtensionInterface;
use Twig\Node\Expression\Binary\AbstractBinary;
use Twig\Node\Expression\Unary\AbstractUnary;

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
     * @return array{
     *     array<string, array{precedence: int, class: class-string<AbstractUnary>}>,
     *     array<string, array{precedence: int, class: class-string<AbstractBinary>, associativity: ExpressionParser::OPERATOR_*}>
     * }
     */
    public function getOperators(): array
    {
        return [[], []];
    }
}
