<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\TrailingSpace;

use TwigCsFixer\Sniff\TrailingSpaceSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

/**
 * Test of TrailingSpaceSniff.
 */
final class TrailingSpaceTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new TrailingSpaceSniff(), [
            [2 => 33],
            [4 => 23],
        ]);

        $this->checkSniff(new TrailingSpaceSniff(), [
            [2 => 32],
            [4 => 21],
        ], __DIR__.'/TrailingSpaceTest2.twig');
    }
}
