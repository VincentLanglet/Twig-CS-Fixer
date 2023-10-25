<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Standard;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Sniff\DelimiterSpacingSniff;
use TwigCsFixer\Sniff\OperatorSpacingSniff;
use TwigCsFixer\Sniff\PunctuationSpacingSniff;
use TwigCsFixer\Standard\Twig;

final class TwigTest extends TestCase
{
    public function testGetSniffs(): void
    {
        $standard = new Twig();

        static::assertEquals([
            new DelimiterSpacingSniff(),
            new OperatorSpacingSniff(),
            new PunctuationSpacingSniff(),
        ], $standard->getSniffs());
    }
}
