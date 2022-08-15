<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\TrailingSpace;

use TwigCsFixer\Sniff\TrailingSpaceSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

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

        // Check empty file
        $this->checkSniff(
            new TrailingSpaceSniff(),
            [],
            __DIR__.'/TrailingSpaceTest3.twig'
        );
    }
}
