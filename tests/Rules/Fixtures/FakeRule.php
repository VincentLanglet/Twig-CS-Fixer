<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Fixtures;

use TwigCsFixer\Rules\AbstractRule;

/**
 * This rule reports an error for the first token of every line.
 */
class FakeRule extends AbstractRule
{
    public function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (1 === $token->getPosition()) {
            $this->addError('First token of the line', $token);
        }
    }
}
