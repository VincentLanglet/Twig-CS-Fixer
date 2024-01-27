<?php

declare(strict_types=1);

namespace Rules\File\FileExtension;

use TwigCsFixer\Rules\File\FileExtensionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class FileExtensionRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new FileExtensionRule(), [
            'FileExtension.Error',
        ]);
    }

    public function testRuleValidFile(): void
    {
        $this->checkRule(new FileExtensionRule(), [], __DIR__.'/file_extension_rule_test.html.twig');
    }

    public function testRuleValidDotFile(): void
    {
        $this->checkRule(new FileExtensionRule(), [], __DIR__.'/.dotfile.twig');
    }

    public function testRuleValidDotFileWithExtension(): void
    {
        $this->checkRule(new FileExtensionRule(), [], __DIR__.'/.dotfile.html.twig');
    }

    public function testRuleMissingExtension(): void
    {
        $this->checkRule(new FileExtensionRule(), ['FileExtension.Error'], __DIR__.'/file_extension_rule_test_missing.twig');
    }
}
