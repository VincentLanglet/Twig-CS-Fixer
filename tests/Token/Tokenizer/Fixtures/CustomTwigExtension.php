<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Token\Tokenizer\Fixtures;

use Twig\ExpressionParser\ExpressionParserInterface;
use Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser;
use Twig\ExpressionParser\Prefix\UnaryOperatorExpressionParser;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\Binary\AddBinary;
use Twig\Node\Expression\Unary\NotUnary;

final class CustomTwigExtension extends AbstractExtension
{
    private const LEGACY_OPERATOR_RIGHT = 2;

    /**
     * @return array{
     *     array<string, array{precedence: int, class: class-string<NotUnary>}>,
     *     array<string, array{precedence: int, class: class-string<AddBinary>, associativity: 2}>
     * }
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
                'associativity' => self::LEGACY_OPERATOR_RIGHT,
            ]],
        ];
    }

    /**
     * @return array<ExpressionParserInterface>
     */
    public function getExpressionParsers(): array
    {
        return [
            new UnaryOperatorExpressionParser(NotUnary::class, 'n0t', 0), // @phpstan-ignore new.internalClass, method.internalClass
            new BinaryOperatorExpressionParser(AddBinary::class, '+sum', 0), // @phpstan-ignore new.internalClass, method.internalClass
        ];
    }
}
