<?php

declare(strict_types=1);

namespace TwigCsFixer\Token;

use Twig\Source;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\ViolationId;

interface TokenizerInterface
{
    /**
     * @return array{Tokens, list<ViolationId>}
     *
     * @throws CannotTokenizeException
     */
    public function tokenize(Source $source): array;
}
