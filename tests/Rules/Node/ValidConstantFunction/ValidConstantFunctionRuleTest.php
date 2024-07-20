<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ValidConstantFunction;

use TwigCsFixer\Rules\Node\ValidConstantFunctionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ValidConstantFunctionRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new ValidConstantFunctionRule(), [
            'ValidConstantFunction.ConstantUndefined:6' => 'Constant "ThisDoesNotExist::SomeKey" is undefined.',
            'ValidConstantFunction.ArgumentNotConstant:7' => 'Function "constant" expects a constant (string value) as argument.',
            'ValidConstantFunction.ClassConstant:8' => 'You cannot use the Twig function "constant()" to access "ThisDoesNotExist::class". You could provide an object and call constant("class", $object) or use the class name directly as a string.',
        ]);
    }
}
