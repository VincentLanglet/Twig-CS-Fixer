<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Environment;

/**
 * List of regex needed by the Tokenizer.
 */
class TokenizerHelper
{
    private const COMMENT_START = '{#';
    private const COMMENT_END = '#}';
    private const BLOCK_START = '{%';
    private const BLOCK_END = '%}';
    private const VARIABLE_START = '{{';
    private const VARIABLE_END = '}}';
    private const INTERPOLATION_START = '#{';
    private const INTERPOLATION_END = '}';
    private const WHITESPACE_TRIM = '-';
    private const WHITESPACE_LINE_TRIM = '~';

    /**
     * @return string
     */
    public static function getBlockEndRegex(): string
    {
        return '/'
            .'(?:'.self::WHITESPACE_TRIM.'|'.self::WHITESPACE_LINE_TRIM.')?'
            .'(?:'.self::BLOCK_END.')'
            .'/A';
    }

    /**
     * @return string
     */
    public static function getCommentEndRegex(): string
    {
        return '/'
            .'(?:'.self::WHITESPACE_TRIM.'|'.self::WHITESPACE_LINE_TRIM.')?'
            .'(?:'.self::COMMENT_END.')'
            .'/'; // Should not be anchored
    }

    /**
     * @return string
     */
    public static function getVariableEndRegex(): string
    {
        return '/'
            .'(?:'.self::WHITESPACE_TRIM.'|'.self::WHITESPACE_LINE_TRIM.')?'
            .'(?:'.self::VARIABLE_END.')'
            .'/A';
    }

    /**
     * @return string
     */
    public static function getExpressionStartRegex(): string
    {
        return '/'
            .'('.self::VARIABLE_START.'|'.self::BLOCK_START.'|'.self::COMMENT_START.')'
            .'('.self::WHITESPACE_TRIM.'|'.self::WHITESPACE_LINE_TRIM.')?'
            .'/';
    }

    /**
     * @return string
     */
    public static function getInterpolationStartRegex(): string
    {
        return '/'.self::INTERPOLATION_START.'/A';
    }

    /**
     * @return string
     */
    public static function getInterpolationEndRegex(): string
    {
        return '/'.self::INTERPOLATION_END.'/A';
    }

    /**
     * @param Environment $env
     *
     * @return string
     */
    public static function getOperatorRegex(Environment $env): string
    {
        /** @psalm-suppress InternalMethod */
        $unaryOperators = $env->getUnaryOperators();
        /** @psalm-suppress InternalMethod */
        $binaryOperators = $env->getBinaryOperators();

        /** @var string[] $operators */
        $operators = array_merge(
            ['=', '?', '?:'],
            array_keys($unaryOperators),
            array_keys($binaryOperators)
        );

        $lengthByOperator = [];
        foreach ($operators as $operator) {
            $lengthByOperator[$operator] = strlen($operator);
        }
        arsort($lengthByOperator);

        $regex = [];
        foreach ($lengthByOperator as $operator => $length) {
            if (ctype_alpha($operator[$length - 1])) {
                $r = preg_quote($operator, '/').'(?=[\s()])';
            } else {
                $r = preg_quote($operator, '/');
            }

            $r = preg_replace('/\s+/', '\s+', $r);

            $regex[] = $r;
        }

        return '/'.implode('|', $regex).'/A';
    }
}
