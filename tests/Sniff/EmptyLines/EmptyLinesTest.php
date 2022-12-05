<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\EmptyLines;

use TwigCsFixer\Sniff\EmptyLinesSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class EmptyLinesTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new EmptyLinesSniff(), [
            [2 => 1],
            [5  => 1],
            [10 => 1],
        ]);
    }
}
