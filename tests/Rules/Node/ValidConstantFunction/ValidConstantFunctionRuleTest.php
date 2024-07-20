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
            'ValidConstantFunction.ClassConstant:9' => 'You cannot use the Twig function "constant()" to access "ThisDoesNotExist::class". You could provide an object and call constant("class", $object) or use the class name directly as a string.',
        ]);
    }
}
