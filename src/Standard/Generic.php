<?php

namespace TwigCsFixer\Standard;

use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Sniff\EmptyLinesSniff;
use TwigCsFixer\Sniff\OperatorSpacingSniff;
use TwigCsFixer\Sniff\PunctuationSpacingSniff;
use TwigCsFixer\Sniff\SniffInterface;

/**
 * Default standard for twig.
 */
class Generic implements StandardInterface
{
    /**
     * @return SniffInterface[]
     */
    public function getSniffs(): array
    {
        return [
            new BlankEOFSniff(),
            new DelimiterSpacingSniff(),
            new EmptyLinesSniff(),
            new OperatorSpacingSniff(),
            new PunctuationSpacingSniff(),
        ];
    }
}
