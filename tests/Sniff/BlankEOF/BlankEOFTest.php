<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\BlankEOF;

use TwigCsFixer\Sniff\BlankEOFSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

/**
 * Test of BlankEOFSniff.
 */
final class BlankEOFTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new BlankEOFSniff(), [
            [4 => 1],
        ]);

        $this->checkSniff(new BlankEOFSniff(), [
            [2 => 7],
        ], __DIR__.'/BlankEOFTest2.twig');
    }

    public function testSniffForEmptyFile(): void
    {
        $this->checkSniff(new BlankEOFSniff(), [], __DIR__.'/BlankEOFTest.empty.twig');
    }
}
