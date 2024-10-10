<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\File\FileName;

use TwigCsFixer\Rules\File\FileNameRule;
use TwigCsFixer\Test\AbstractRuleTestCase;

final class FileNameRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'case' => FileNameRule::SNAKE_CASE,
                'baseDirectory' => null,
                'ignoredSubDirectories' => [],
                'optionalPrefix' => '',
            ],
            (new FileNameRule())->getConfiguration()
        );

        static::assertSame(
            [
                'case' => FileNameRule::PASCAL_CASE,
                'baseDirectory' => 'foo',
                'ignoredSubDirectories' => ['bar'],
                'optionalPrefix' => '_',
            ],
            (new FileNameRule(
                FileNameRule::PASCAL_CASE,
                'foo',
                ['bar'],
                '_'
            ))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new FileNameRule(), [
            'FileName.Error' => 'The file name must use snake_case; expected file_name_rule_test.',
        ]);
    }

    public function testRuleDotfile(): void
    {
        $this->checkRule(new FileNameRule(), [
            'FileName.Error' => 'The file name must use snake_case; expected file_name_rule_test.',
        ], __DIR__.'/.FileNameRuleTest.twig');
    }

    public function testRuleValidDotfile(): void
    {
        $this->checkRule(new FileNameRule(FileNameRule::PASCAL_CASE), [], __DIR__.'/.FileNameRuleTest.twig');
    }

    public function testRulePascalCase(): void
    {
        $this->checkRule(new FileNameRule(FileNameRule::PASCAL_CASE), []);
    }

    public function testRuleKebabCase(): void
    {
        $this->checkRule(new FileNameRule(FileNameRule::KEBAB_CASE), [], __DIR__.'/file-name-rule-test.twig');
    }

    public function testRuleCamelCase(): void
    {
        $this->checkRule(new FileNameRule(FileNameRule::CAMEL_CASE), [], __DIR__.'/fileNameRuleTest.camel.twig');
    }

    public function testRuleValidFile(): void
    {
        $this->checkRule(new FileNameRule(), [], __DIR__.'/file_name_rule_test.twig');
    }

    public function testRuleValidFileWithDot(): void
    {
        $this->checkRule(new FileNameRule(), [], __DIR__.'/file_name_rule_test.withDot.twig');
    }

    public function testRuleBaseDir(): void
    {
        $this->checkRule(new FileNameRule(baseDirectory: __DIR__.'/..'), [
            'FileName.Error' => 'The file name must use snake_case; expected file_name_rule_test.',
        ]);
    }

    public function testRuleIgnoredPath(): void
    {
        $this->checkRule(new FileNameRule(baseDirectory: __DIR__.'/..', ignoredSubDirectories: ['FileName']), []);
    }

    public function testRuleOptionalPrefix(): void
    {
        $this->checkRule(new FileNameRule(), [
            'FileName.Error' => 'The file name must use snake_case; expected file_name_rule_test.',
        ], __DIR__.'/_file_name_rule_test.twig');

        $this->checkRule(new FileNameRule(optionalPrefix: '_'), [], __DIR__.'/_file_name_rule_test.twig');

        $this->checkRule(new FileNameRule(FileNameRule::CAMEL_CASE, optionalPrefix: '_'), [
            'FileName.Error' => 'The file name must use camelCase; expected _fileNameRuleTest.',
        ], __DIR__.'/_file_name_rule_test.twig');
    }
}
