<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\File\DirectoryName;

use TwigCsFixer\Rules\File\DirectoryNameRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class DirectoryNameRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'case' => DirectoryNameRule::SNAKE_CASE,
                'baseDirectory' => null,
                'ignoredSubDirectories' => [],
                'optionalPrefix' => ''
            ],
            (new DirectoryNameRule())->getConfiguration()
        );

        static::assertSame(
            [
                'case' => DirectoryNameRule::PASCAL_CASE,
                'baseDirectory' => 'foo',
                'ignoredSubDirectories' => ['bar'],
                'optionalPrefix' => 'baz'
            ],
            (new DirectoryNameRule(
                DirectoryNameRule::PASCAL_CASE,
                'foo',
                ['bar'],
                'baz'
            ))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new DirectoryNameRule(baseDirectory: __DIR__.'/templates'), []);
    }

    public function testRuleValidTemplatesDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: __DIR__.'/templates'),
            [],
            __DIR__.'/templates/directory_name_rule_test/DirectoryNameRuleTest.twig'
        );
    }

    public function testRuleInvalidTemplatesDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: __DIR__.'/templates'),
            [
                'DirectoryName.Error' => 'The directory name must use snake_case; expected directory_name_rule_test.',
            ],
            __DIR__.'/templates/directoryNameRuleTest/DirectoryNameRuleTest.twig'
        );
    }

    public function testRuleValidPrefixDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: __DIR__.'/templates', optionalPrefix: '_'),
            [],
            __DIR__.'/templates/_directory_name_rule_test/DirectoryNameRuleTest.twig'
        );
    }

    public function testRuleInvalidPrefixDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: __DIR__.'/templates', optionalPrefix: '!'),
            [
                'DirectoryName.Error' => 'The directory name must use snake_case; expected directory_name_rule_test.',
            ],
            __DIR__.'/templates/_directory_name_rule_test/DirectoryNameRuleTest.twig'
        );
    }

    public function testRulePascalCase(): void
    {
        $this->checkRule(new DirectoryNameRule(DirectoryNameRule::PASCAL_CASE, baseDirectory: __DIR__.'/..'), []);
    }

    public function testRuleInvalidDirectory(): void
    {
        $this->checkRule(new DirectoryNameRule(baseDirectory: __DIR__.'/..'), [
            'DirectoryName.Error' => 'The directory name must use snake_case; expected directory_name.',
        ]);
    }

    public function testRuleKebabCase(): void
    {
        $this->checkRule(
            new DirectoryNameRule(DirectoryNameRule::KEBAB_CASE, baseDirectory: __DIR__.'/templates'),
            [],
            __DIR__.'/templates/directory-name-rule-test/DirectoryNameRuleTest.twig',
        );
    }

    public function testRuleCamelCase(): void
    {
        $this->checkRule(
            new DirectoryNameRule(DirectoryNameRule::CAMEL_CASE, baseDirectory: __DIR__.'/templates'),
            [],
            __DIR__.'/templates/directoryNameRuleTest/DirectoryNameRuleTest.twig',
        );
    }

    public function testRuleIgnoredDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: __DIR__.'/templates', ignoredSubDirectories: ['bundles']),
            [],
            __DIR__.'/templates/bundles/directoryNameRuleTest/DirectoryNameRuleTest.twig'
        );
    }
}
