<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\TrailingCommaSingleLine;

use TwigCsFixer\Sniff\TrailingCommaSingleLineSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTest;

/**
 * Test of TrailingCommaSingleLine.
 */
final class TrailingCommaSingleLineTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new TrailingCommaSingleLineSniff(), [
            [2 => 9],
            [4 => 13],
            [6 => 12],
        ]);
    }
}
