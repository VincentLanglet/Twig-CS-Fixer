<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\DelimiterSpacing;

use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Tests\Ruleset\AbstractSniffTest;

/**
 * Test of DelimiterSpacingSniff.
 */
final class DelimiterSpacingTest extends AbstractSniffTest
{
    /**
     * @return void
     */
    public function testSniff(): void
    {
        $this->checkSniff(new DelimiterSpacingSniff(), [
            [15 => 1],
            [15 => 12],
            [15 => 15],
            [15 => 25],
        ]);
    }
}
