<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\BlockNameSpacingSniff;

use TwigCsFixer\Sniff\BlockNameSpacingSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class BlockNameSpacingTest extends AbstractSniffTestCase
{
    public function testSniff(): void
    {
        $this->checkSniff(new BlockNameSpacingSniff(), [
            [1 => 5],
            [1 => 5],
            [3 => 3],
            [3 => 3],
        ]);
    }
}
