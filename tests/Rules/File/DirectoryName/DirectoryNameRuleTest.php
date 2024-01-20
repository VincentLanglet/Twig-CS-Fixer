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
                'case'                  => DirectoryNameRule::SNAKE_CASE,
                'baseDirectory'         => null,
                'ignoredSubDirectories' => [],
            ],
            (new DirectoryNameRule())->getConfiguration()
        );

        static::assertSame(
            [
                'case'                  => DirectoryNameRule::PASCAL_CASE,
                'baseDirectory'         => 'foo',
                'ignoredSubDirectories' => ['bar'],
            ],
            (new DirectoryNameRule(
                DirectoryNameRule::PASCAL_CASE,
                'foo',
                ['bar']
            ))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new DirectoryNameRule(baseDirectory: 'templates'), []);
    }

    public function testRuleValidTemplatesDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: 'templates'),
            [],
            __DIR__.'/templates/directory_name_rule_test/DirectoryNameRuleTest.twig'
        );
    }

    public function testRuleInvalidTemplatesDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: 'templates'),
            ['DirectoryName.Error'],
            __DIR__.'/templates/directoryNameRuleTest/DirectoryNameRuleTest.twig'
        );
    }

    public function testRulePascalCase(): void
    {
        $this->checkRule(new DirectoryNameRule(DirectoryNameRule::PASCAL_CASE, baseDirectory: 'File'), []);
    }

    public function testRuleInvalidDirectory(): void
    {
        $this->checkRule(new DirectoryNameRule(baseDirectory: 'File'), ['DirectoryName.Error']);
    }

    public function testRuleKebabCase(): void
    {
        $this->checkRule(
            new DirectoryNameRule(DirectoryNameRule::KEBAB_CASE, baseDirectory: 'templates'),
            [],
            __DIR__.'/templates/directory-name-rule-test/DirectoryNameRuleTest.twig',
        );
    }

    public function testRuleCamelCase(): void
    {
        $this->checkRule(
            new DirectoryNameRule(DirectoryNameRule::CAMEL_CASE, baseDirectory: 'templates'),
            [],
            __DIR__.'/templates/directoryNameRuleTest/DirectoryNameRuleTest.twig',
        );
    }

    public function testRuleIgnoredDirectory(): void
    {
        $this->checkRule(
            new DirectoryNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['bundles']),
            [],
            __DIR__.'/templates/bundles/directoryNameRuleTest/DirectoryNameRuleTest.twig'
        );
    }
}
