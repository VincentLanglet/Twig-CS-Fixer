<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\File\FileExtension;

use TwigCsFixer\Rules\File\FileExtensionRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class FileExtensionRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new FileExtensionRule(), [
            'FileExtension.Error' => 'The file must use two extensions; found ".twig".',
        ]);
    }

    public function testRuleIgnoredFile(): void
    {
        $this->checkRule(new FileExtensionRule(), [], __DIR__.'/FileExtensionRuleTest.php');
    }

    public function testRuleValidFile(): void
    {
        $this->checkRule(new FileExtensionRule(), [], __DIR__.'/FileExtensionRuleTest.html.twig');
    }

    public function testRuleInvalidDotFile(): void
    {
        $this->checkRule(new FileExtensionRule(), [
            'FileExtension.Error' => 'The file must use two extensions; found ".twig".',
        ], __DIR__.'/.dotfile.twig');
    }

    public function testRuleValidDotFileWithFormatExtension(): void
    {
        $this->checkRule(new FileExtensionRule(), [], __DIR__.'/.dotfile.html.twig');
    }
}
