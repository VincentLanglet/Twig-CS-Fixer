<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Standard;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\DelimiterSpacingRule;
use TwigCsFixer\Rules\OperatorNameSpacingRule;
use TwigCsFixer\Rules\OperatorSpacingRule;
use TwigCsFixer\Rules\PunctuationSpacingRule;
use TwigCsFixer\Standard\Twig;

final class TwigTest extends TestCase
{
    public function testGetRules(): void
    {
        $standard = new Twig();

        static::assertEquals([
            new DelimiterSpacingRule(),
            new OperatorNameSpacingRule(),
            new OperatorSpacingRule(),
            new PunctuationSpacingRule(),
        ], $standard->getRules());
    }
}
