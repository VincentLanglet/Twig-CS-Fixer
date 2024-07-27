<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Source;
use TwigCsFixer\Exception\CannotTokenizeException;

interface TokenizerInterface
{
    /**
     * @throws CannotTokenizeException
     */
    public function tokenize(Source $source): Tokens;
}
