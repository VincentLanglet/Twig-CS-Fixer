<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token\Tokenizer\Fixtures;

use Twig\ExpressionParser;
use Twig\Extension\ExtensionInterface;
use Twig\Node\Expression\Binary\AddBinary;
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
     * @phpstan-ignore missingType.iterableValue
     */
    public function getOperators(): array
    {
        return [
            ['n0t' => [
                'precedence' => 0,
                'class' => NotUnary::class,
            ]],
            ['+sum' => [
                'precedence' => 0,
                'class' => AddBinary::class,
                'associativity' => ExpressionParser::OPERATOR_RIGHT,
            ]],
        ];
    }
}
