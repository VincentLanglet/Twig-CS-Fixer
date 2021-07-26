<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Environment;

/**
 * Class TokenizerHelper
 *
 * @phpstan-import-type TokenizerOptions from Tokenizer
 */
class TokenizerHelper
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var array<string, string|string[]>
     *
     * @phpstan-var TokenizerOptions
     */
    private $options;

    /**
     * @param Environment                    $env
     * @param array<string, string|string[]> $options
     *
     * @return void
     *
     * @phpstan-param TokenizerOptions $options
     */
    public function __construct(Environment $env, array $options)
    {
        $this->env = $env;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getBlockRegex(): string
    {
        return '/'
            .'('
                .preg_quote($this->options['whitespace_trim'])
                .'|'.preg_quote($this->options['whitespace_line_trim'])
            .')?'
            .'('
                .preg_quote($this->options['tag_block'][1])
            .')'
            .'/A';
    }

    /**
     * @return string
     */
    public function getCommentRegex(): string
    {
        return '/'
            .'('
                .preg_quote($this->options['whitespace_trim'])
                .'|'.preg_quote($this->options['whitespace_line_trim'])
            .')?'
            .'('
                .preg_quote($this->options['tag_comment'][1])
            .')'
            .'/'; // Should not be anchored
    }

    /**
     * @return string
     */
    public function getVariableRegex(): string
    {
        return '/'
            .'('
                .preg_quote($this->options['whitespace_trim'])
                .'|'.preg_quote($this->options['whitespace_line_trim'])
            .')?'
            .'('
                .preg_quote($this->options['tag_variable'][1])
            .')'
            .'/A';
    }

    /**
     * @return string
     */
    public function getTokensStartRegex(): string
    {
        return '/'
            .'('
                .preg_quote($this->options['tag_variable'][0])
                .'|'.preg_quote($this->options['tag_block'][0])
                .'|'.preg_quote($this->options['tag_comment'][0])
            .')'
            .'('
                .preg_quote($this->options['whitespace_trim'])
                .'|'.preg_quote($this->options['whitespace_line_trim'])
            .')?'
            .'/';
    }

    /**
     * @return string
     */
    public function getInterpolationStartRegex(): string
    {
        return '/'
            .'('
                .preg_quote($this->options['interpolation'][0])
            .')'
            .'/A';
    }

    /**
     * @return string
     */
    public function getInterpolationEndRegex(): string
    {
        return '/'
            .'('
                .preg_quote($this->options['interpolation'][1])
            .')'
            .'/A';
    }

    /**
     * @return string
     */
    public function getOperatorRegex(): string
    {
        /** @psalm-suppress InternalMethod */
        $unaryOperators = $this->env->getUnaryOperators();
        /** @psalm-suppress InternalMethod */
        $binaryOperators = $this->env->getBinaryOperators();

        /** @var string[] $operators */
        $operators = array_merge(
            ['=', '?', '?:'],
            array_keys($unaryOperators),
            array_keys($binaryOperators)
        );

        $lengthByOperator = [];
        foreach ($operators as $operator) {
            $lengthByOperator[$operator] = mb_strlen($operator);
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
