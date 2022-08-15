<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\DelimiterSpacing;

use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class DelimiterSpacingTest extends AbstractSniffTestCase
{
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
