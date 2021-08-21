<?php

namespace TwigCsFixer\Token;

use Twig\Error\SyntaxError;
use Twig\Source;

/**
 * Interface for Tokenizer.
 */
interface TokenizerInterface
{
    /**
     * @param Source $source
     *
     * @return list<Token>
     *
     * @throws SyntaxError
     */
    public function tokenize(Source $source): array;
}
