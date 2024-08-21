<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ValidConstantFunction;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use TwigCsFixer\Rules\Node\ValidConstantFunctionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ValidConstantFunctionRuleTest extends AbstractRuleTestCase
{
    public const SOME_CONSTANT = 'Foo';

    protected function setUp(): void
    {
        parent::setUp();

        if (!InstalledVersions::satisfies(new VersionParser(), 'twig/twig', '>=3.10.0')) {
            static::markTestSkipped('twig/twig ^3.10.0 is required.');
        }
    }

    public function testRule(): void
    {
        $this->checkRule(new ValidConstantFunctionRule(), [
            'ValidConstantFunction.ConstantUndefined:7' => 'Constant "ThisDoesNotExist::SomeKey" is undefined.',
            'ValidConstantFunction.ClassConstant:9' => 'You cannot use the function "constant()" to resolve class names.',
            'ValidConstantFunction.ClassConstant:10' => 'You cannot use the function "constant()" to resolve class names.',
            'ValidConstantFunction.StringConstant:11' => 'The first param of the function "constant()" must be a string.',
            'ValidConstantFunction.ConstantUndefined:18' => 'Constant "ThisDoesNotExist::SomeKey" is undefined.',
            'ValidConstantFunction.ClassConstant:20' => 'You cannot use the function "constant()" to resolve class names.',
            'ValidConstantFunction.ClassConstant:21' => 'You cannot use the function "constant()" to resolve class names.',
            'ValidConstantFunction.StringConstant:22' => 'The first param of the function "constant()" must be a string.',
            'ValidConstantFunction.NoConstant:24' => 'The first param of the function "constant()" is required.',
        ]);
    }
}
