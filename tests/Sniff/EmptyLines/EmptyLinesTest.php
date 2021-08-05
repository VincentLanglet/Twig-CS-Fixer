<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\EmptyLines;

use TwigCsFixer\Sniff\EmptyLinesSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTest;

/**
 * Test of EmptyLinesSniff.
 */
final class EmptyLinesTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new EmptyLinesSniff(), [
            [3 => 1],
        ]);
    }
}
