<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Fixtures;

use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Token\Tokens;

/**
 * This rule reports an error for the first token of every line.
 */
final class FakeRule extends AbstractRule
{
    public function process(int $tokenPosition, Tokens $tokens): void
    {
        $token = $tokens->get($tokenPosition);
        if (1 === $token->getPosition()) {
            $this->addError('First token of the line', $token);
        }
    }
}
