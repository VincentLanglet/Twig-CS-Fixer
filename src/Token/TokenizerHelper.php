<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Environment;

/**
 * Class TokenizerHelper
 */
class TokenizerHelper
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param Environment $env
     * @param array       $options
     *
     * @return void
     */
    public function __construct(Environment $env, array $options = [])
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
        $operators = array_merge(
            ['=', '?', '?:'],
            array_keys($this->env->getUnaryOperators()),
            array_keys($this->env->getBinaryOperators())
        );

        $operators = array_combine($operators, array_map('strlen', $operators));
        arsort($operators);

        $regex = [];
        foreach ($operators as $operator => $length) {
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
