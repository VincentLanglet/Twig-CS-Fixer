<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\OperatorNameSpacing;

use TwigCsFixer\Sniff\OperatorNameSpacingSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class OperatorNameSpacingTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new OperatorNameSpacingSniff(), [
            [2 => 13],
            [3 => 13],
            [4 => 10],
        ]);
    }
}
