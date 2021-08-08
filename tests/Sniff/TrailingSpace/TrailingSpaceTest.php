<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\TrailingSpace;

use TwigCsFixer\Sniff\TrailingSpaceSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTest;

/**
 * Test of TrailingSpaceSniff.
 */
final class TrailingSpaceTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new TrailingSpaceSniff(), [
            [2 => 33],
            [4 => 23],
        ]);
    }
}
