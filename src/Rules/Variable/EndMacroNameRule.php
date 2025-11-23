<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;

/**
 * Ensures that the name is set at the end of the macro.
 */
final class EndMacroNameRule extends AbstractEndNameRule
{
    protected function getEndName(): string
    {
        return 'endmacro';
    }

    protected function getStartName(): string
    {
        return 'macro';
    }
}
