<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\OperatorSpacing;

use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class OperatorSpacingTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new OperatorSpacingRule(), [
            'OperatorSpacing.After:1:5',
            'OperatorSpacing.Before:1:5',
            'OperatorSpacing.After:2:5',
            'OperatorSpacing.Before:2:5',
            'OperatorSpacing.After:3:5',
            'OperatorSpacing.Before:3:5',
            'OperatorSpacing.After:4:5',
            'OperatorSpacing.Before:4:5',
            'OperatorSpacing.After:5:5',
            'OperatorSpacing.Before:5:5',
            'OperatorSpacing.After:6:5',
            'OperatorSpacing.Before:6:5',
            'OperatorSpacing.After:7:5',
            'OperatorSpacing.Before:7:5',
            'OperatorSpacing.After:8:7',
            'OperatorSpacing.Before:8:7',
            'OperatorSpacing.After:9:10',
            'OperatorSpacing.Before:9:10',
            'OperatorSpacing.After:9:19',
            'OperatorSpacing.Before:9:19',
            'OperatorSpacing.After:10:5',
            'OperatorSpacing.Before:10:5',
            'OperatorSpacing.After:11:4',
            'OperatorSpacing.After:12:11',
            'OperatorSpacing.Before:12:11',
            'OperatorSpacing.After:13:11',
            'OperatorSpacing.Before:13:11',
            'OperatorSpacing.After:14:7',
            'OperatorSpacing.Before:14:7',
            'OperatorSpacing.After:15:7',
            'OperatorSpacing.Before:15:7',
            'OperatorSpacing.After:19:5',
            'OperatorSpacing.Before:19:5',
            'OperatorSpacing.After:20:5',
            'OperatorSpacing.Before:20:5',
            'OperatorSpacing.After:22:6',
            'OperatorSpacing.After:33:10',
            'OperatorSpacing.Before:33:10',
            'OperatorSpacing.After:35:13',
            'OperatorSpacing.Before:35:13',
            'OperatorSpacing.After:36:13',
            'OperatorSpacing.Before:36:13',
            'OperatorSpacing.After:37:13',
            'OperatorSpacing.Before:37:13',
        ]);
    }

    public function testRuleWithTab(): void
    {
        $this->checkRule(
            new OperatorSpacingRule(),
            [],
            __DIR__.'/OperatorSpacingTest.tab.twig'
        );
    }
}
