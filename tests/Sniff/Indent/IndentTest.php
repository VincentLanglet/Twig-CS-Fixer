<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff\Indent;

use TwigCsFixer\Sniff\IndentSniff;
use TwigCsFixer\Tests\Sniff\AbstractSniffTestCase;

final class IndentTest extends AbstractSniffTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(['space_ratio' => 4], (new IndentSniff())->getConfiguration());
        static::assertSame(['space_ratio' => 2], (new IndentSniff(2))->getConfiguration());
    }

    public function testSniff(): void
    {
        $this->checkSniff(new IndentSniff(), [
            [2  => 1],
            [4  => 1],
        ]);
    }

    public function testSniffWithSpaceRatio(): void
    {
        $this->checkSniff(
            new IndentSniff(2),
            [
                [2  => 1],
                [4  => 1],
            ],
            __DIR__.'/IndentTest.twig',
            __DIR__.'/IndentTest.fixed2.twig',
        );
    }
}
