<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\BlankEOF;

use TwigCsFixer\Rules\Whitespace\BlankEOFRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class BlankEOFRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error:4:1',
        ]);

        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error:2:7',
        ], __DIR__.'/BlankEOFRuleTest2.twig');
    }

    public function testRuleForEmptyFile(): void
    {
        $this->checkRule(new BlankEOFRule(), [], __DIR__.'/BlankEOFRuleTest.empty.twig');

        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error:3:1',
        ], __DIR__.'/BlankEOFRuleTest.empty2.twig');
    }
}
