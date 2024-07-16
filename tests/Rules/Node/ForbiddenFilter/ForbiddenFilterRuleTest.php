<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ForbiddenFilter;

use TwigCsFixer\Rules\Node\ForbiddenFilterRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ForbiddenFilterRuleTest extends AbstractRuleTestCase
{
    public function testConfiguration(): void
    {
        static::assertSame(
            [
                'filters' => ['foo'],
            ],
            (new ForbiddenFilterRule(['foo']))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new ForbiddenFilterRule(['trans']), [
            'ForbiddenFilter.Error:2' => 'Filter "trans" is not allowed.',
            'ForbiddenFilter.Error:5' => 'Filter "trans" is not allowed.',
        ]);
    }
}
