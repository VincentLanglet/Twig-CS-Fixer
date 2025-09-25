<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Comment\CommentType;

use TwigCsFixer\Rules\Comment\CommentTypeRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class CommentTypeRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(
            [
                new CommentTypeRule(),
            ],
            [
                // TODO: compute proper file lines/elements
                'CommentType.Error:1:4' => 'Variable comment declaration must be used via types tag instead of comment.',
                'CommentType.Error:2:4' => 'Variable comment declaration must be used via types tag instead of comment.',
                'CommentType.Error:3:4' => 'Variable comment declaration must be used via types tag instead of comment.',
                'CommentType.Error:4:4' => 'Variable comment declaration must be used via types tag instead of comment.',
                'CommentType.Error:5:4' => 'Variable comment declaration must be used via types tag instead of comment.',
            ]
        );
    }
}
