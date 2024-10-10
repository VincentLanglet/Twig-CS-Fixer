<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Whitespace\BlankEOF;

use TwigCsFixer\Rules\Whitespace\BlankEOFRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class BlankEOFRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error:4:1' => 'A file must end with 1 blank line; found 2',
        ]);

        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error:2:7' => 'A file must end with 1 blank line; found 0',
        ], __DIR__.'/BlankEOFRuleTest2.twig');
    }

    public function testRuleForEmptyFile(): void
    {
        $this->checkRule(new BlankEOFRule(), [], __DIR__.'/BlankEOFRuleTest.empty.twig');

        $this->checkRule(new BlankEOFRule(), [
            'BlankEOF.Error:3:1' => 'A file must end with 1 blank line; found 3',
        ], __DIR__.'/BlankEOFRuleTest.empty2.twig');
    }
}
