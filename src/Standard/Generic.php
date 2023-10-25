<?php

declare(strict_types=1);

namespace TwigCsFixer\Standard;

use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Sniff\EmptyLinesSniff;
use TwigCsFixer\Sniff\IndentSniff;
use TwigCsFixer\Sniff\TrailingCommaSingleLineSniff;
use TwigCsFixer\Sniff\TrailingSpaceSniff;

/**
 * Default standard from this fixer.
 */
final class Generic implements StandardInterface
{
    public function getSniffs(): array
    {
        return [
            ...(new Twig())->getSniffs(),
            new BlankEOFSniff(),
            new EmptyLinesSniff(),
            new IndentSniff(),
            new TrailingCommaSingleLineSniff(),
            new TrailingSpaceSniff(),
        ];
    }
}
