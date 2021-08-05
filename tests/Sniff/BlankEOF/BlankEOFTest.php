<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\BlankEOF;

use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTest;

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

    /**
     * @return void
     */
    public function testSniffForEmptyFile(): void
    {
        $this->checkSniff(new BlankEOFSniff(), [], __DIR__.'/BlankEOFTest.empty.twig');
    }
}
