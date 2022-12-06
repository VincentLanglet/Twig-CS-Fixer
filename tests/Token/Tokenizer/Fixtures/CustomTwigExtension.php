<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token\Tokenizer\Fixtures;

use Twig\ExpressionParser;
use Twig\Extension\ExtensionInterface;
use Twig\Node\Expression\Binary\AbstractBinary;
use Twig\Node\Expression\Binary\AddBinary;
use Twig\Node\Expression\Unary\AbstractUnary;
use Twig\Node\Expression\Unary\NotUnary;

final class CustomTwigExtension implements ExtensionInterface
{
    public function getTokenParsers(): array
    {
        return [];
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
     * @phpstan-return array{
     *     array<string, array{precedence: int, class: class-string<AbstractUnary>}>,
     *     array<string, array{precedence: int, class: class-string<AbstractBinary>, associativity: ExpressionParser::OPERATOR_*}>
     * }
     */
    public function getOperators(): array
    {
        /** @psalm-suppress InternalClass */
        return [
            ['n0t' => [
                'precedence' => 0,
                'class'      => NotUnary::class,
            ]],
            ['+sum' => [
                'precedence'    => 0,
                'class'         => AddBinary::class,
                'associativity' => ExpressionParser::OPERATOR_RIGHT,
            ]],
        ];
    }
}
