<?php

declare(strict_types=1);

namespace Rules\File\FileExtension;

use TwigCsFixer\Rules\File\FileExtensionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class FileExtensionRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'baseDirectory'         => null,
                'ignoredSubDirectories' => [],
            ],
            (new FileExtensionRule())->getConfiguration()
        );

        static::assertSame(
            [
                'baseDirectory'         => 'foo',
                'ignoredSubDirectories' => ['bar'],
            ],
            (new FileExtensionRule(
                'foo',
                ['bar']
            ))->getConfiguration()
        );
    }

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

    public function testRuleMissingExtension(): void
    {
        $this->checkRule(new FileExtensionRule(), ['FileExtension.Error'], __DIR__.'/file_extension_rule_test_missing.twig');
    }

    public function testRuleInvalidFormatExtension(): void
    {
        $this->checkRule(new FileExtensionRule(), ['FileExtension.Error'], __DIR__.'/file_extension_rule_test.invalid.twig');
    }

    public function testRuleBaseDir(): void
    {
        $this->checkRule(new FileExtensionRule(baseDirectory: 'File'), [
            'FileExtension.Error',
        ]);
    }

    public function testRuleIgnoredPath(): void
    {
        $this->checkRule(new FileExtensionRule(baseDirectory: 'File', ignoredSubDirectories: ['FileExtension']), []);
    }
}
