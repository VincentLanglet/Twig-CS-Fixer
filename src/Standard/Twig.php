<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Sniff\OperatorSpacingSniff;
use TwigCsFixer\Sniff\PunctuationSpacingSniff;

/**
 * Standard from twig.
 *
 * @see https://twig.symfony.com/doc/3.x/coding_standards.html
 */
class Twig implements StandardInterface
{
    public function getSniffs(): array
    {
        return [
            new DelimiterSpacingSniff(),
            new OperatorSpacingSniff(),
            new PunctuationSpacingSniff(),
        ];
    }
}
