<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ValidConstantFunction;

use TwigCsFixer\Rules\Node\ValidConstantFunctionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ValidConstantFunctionRuleTest extends AbstractRuleTestCase
{
    public const SOME_CONSTANT = 'Foo';

    public function testRule(): void
    {
        $this->checkRule(new ValidConstantFunctionRule(), [
            'ValidConstantFunction.ConstantUndefined:7' => 'Constant "ThisDoesNotExist::SomeKey" is undefined.',
            'ValidConstantFunction.ClassConstant:9' => 'You cannot use the function "constant()" to resolve class names.',
            'ValidConstantFunction.StringConstant:10' => 'The first param of the function "constant()" must be a string.',
            'ValidConstantFunction.ConstantUndefined:17' => 'Constant "ThisDoesNotExist::SomeKey" is undefined.',
            'ValidConstantFunction.ClassConstant:19' => 'You cannot use the function "constant()" to resolve class names.',
            'ValidConstantFunction.StringConstant:20' => 'The first param of the function "constant()" must be a string.',
        ]);
    }
}
