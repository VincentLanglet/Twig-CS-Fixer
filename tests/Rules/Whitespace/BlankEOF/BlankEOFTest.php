<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\BlankEOF;

use TwigCsFixer\Rules\Whitespace\BlankEOFRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class BlankEOFTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error.Eof:4:1',
        ]);

        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error.Eof:2:7',
        ], __DIR__.'/BlankEOFTest2.twig');
    }

    public function testRuleForEmptyFile(): void
    {
        $this->checkRule(new BlankEOFRule(), [], __DIR__.'/BlankEOFTest.empty.twig');

        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error.Eof:3:1',
        ], __DIR__.'/BlankEOFTest.empty2.twig');
    }
}
