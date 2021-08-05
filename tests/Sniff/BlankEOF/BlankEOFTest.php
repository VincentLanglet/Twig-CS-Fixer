<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\BlankEOF;

use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Tests\Ruleset\AbstractSniffTest;

/**
 * Test of BlankEOFSniff.
 */
final class BlankEOFTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new BlankEOFSniff(), [
            [4 => 1],
        ]);
    }
}
