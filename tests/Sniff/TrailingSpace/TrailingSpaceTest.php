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
    }

    public function testSniffWithTab(): void
    {
        $this->checkSniff(new TrailingSpaceSniff(), [
            [2 => 32],
            [4 => 21],
        ], __DIR__.'/TrailingSpaceTest.tab.twig');
    }

    public function testSniffWithEmptyFile(): void
    {
        $this->checkSniff(
            new TrailingSpaceSniff(),
            [],
            __DIR__.'/TrailingSpaceTest.empty.twig'
        );

        $this->checkSniff(new TrailingSpaceSniff(), [
            [1 => 1],
        ], __DIR__.'/TrailingSpaceTest.empty2.twig');
    }
}
