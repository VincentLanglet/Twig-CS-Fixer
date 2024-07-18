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
    public function process(int $tokenIndex, Tokens $tokens): void
    {
        $token = $tokens->get($tokenIndex);
        if (1 === $token->getLinePosition()) {
            $this->addError('First token of the line', $token);
        }
    }
}
