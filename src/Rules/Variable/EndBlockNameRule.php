<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Variable;


/**
 * Ensures that the name is set at the end of the block.
 */
final class EndBlockNameRule extends AbstractEndNameRule
{
    protected function getEndName(): string
    {
        return 'endblock';
    }

    protected function getStartName(): string
    {
        return 'block';
    }
}
