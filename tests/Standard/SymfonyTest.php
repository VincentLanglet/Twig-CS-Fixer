<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Standard;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Rules\Delimiter\DelimiterSpacingRule;
use TwigCsFixer\Rules\File\DirectoryNameRule;
use TwigCsFixer\Rules\File\FileExtensionRule;
use TwigCsFixer\Rules\File\FileNameRule;
use TwigCsFixer\Rules\Function\MacroArgumentNameRule;
use TwigCsFixer\Rules\Function\NamedArgumentNameRule;
use TwigCsFixer\Rules\Function\NamedArgumentSeparatorRule;
use TwigCsFixer\Rules\Function\NamedArgumentSpacingRule;
use TwigCsFixer\Rules\Operator\OperatorNameSpacingRule;
use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Rules\Punctuation\PunctuationSpacingRule;
use TwigCsFixer\Rules\Variable\VariableNameRule;
use TwigCsFixer\Standard\Symfony;

final class SymfonyTest extends TestCase
{
    public function testGetRules(): void
    {
        $standard = new Symfony();

        static::assertEquals([
            new DelimiterSpacingRule(),
            new MacroArgumentNameRule(),
            new NamedArgumentNameRule(),
            new NamedArgumentSeparatorRule(),
            new NamedArgumentSpacingRule(),
            new OperatorNameSpacingRule(),
            new OperatorSpacingRule(),
            new PunctuationSpacingRule(),
            new VariableNameRule(),
            new FileNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['bundles', 'components'], optionalPrefix: '_'),
            new FileNameRule(case: DirectoryNameRule::PASCAL_CASE, baseDirectory: 'templates/components'),
            new DirectoryNameRule(baseDirectory: 'templates', ignoredSubDirectories: ['bundles', 'components']),
            new DirectoryNameRule(case: DirectoryNameRule::PASCAL_CASE, baseDirectory: 'templates/components'),
            new FileExtensionRule(),
        ], $standard->getRules());
    }
}
