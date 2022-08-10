<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Error\SyntaxError;
use Twig\Source;

/**
 * Interface for Tokenizer.
 */
interface TokenizerInterface
{
    /**
     * @return list<Token>
     *
     * @throws SyntaxError
     */
    public function tokenize(Source $source): array;
}
