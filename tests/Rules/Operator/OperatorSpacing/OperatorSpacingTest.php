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
            'OperatorSpacing.After.Operator:1:5',
            'OperatorSpacing.Before.Operator:1:5',
            'OperatorSpacing.After.Operator:2:5',
            'OperatorSpacing.Before.Operator:2:5',
            'OperatorSpacing.After.Operator:3:5',
            'OperatorSpacing.Before.Operator:3:5',
            'OperatorSpacing.After.Operator:4:5',
            'OperatorSpacing.Before.Operator:4:5',
            'OperatorSpacing.After.Operator:5:5',
            'OperatorSpacing.Before.Operator:5:5',
            'OperatorSpacing.After.Operator:6:5',
            'OperatorSpacing.Before.Operator:6:5',
            'OperatorSpacing.After.Operator:7:5',
            'OperatorSpacing.Before.Operator:7:5',
            'OperatorSpacing.After.Operator:8:7',
            'OperatorSpacing.Before.Operator:8:7',
            'OperatorSpacing.After.Operator:9:10',
            'OperatorSpacing.Before.Operator:9:10',
            'OperatorSpacing.After.Operator:9:19',
            'OperatorSpacing.Before.Operator:9:19',
            'OperatorSpacing.After.Operator:10:5',
            'OperatorSpacing.Before.Operator:10:5',
            'OperatorSpacing.After.Operator:11:4',
            'OperatorSpacing.After.Operator:12:11',
            'OperatorSpacing.Before.Operator:12:11',
            'OperatorSpacing.After.Operator:13:11',
            'OperatorSpacing.Before.Operator:13:11',
            'OperatorSpacing.After.Operator:14:7',
            'OperatorSpacing.Before.Operator:14:7',
            'OperatorSpacing.After.Operator:15:7',
            'OperatorSpacing.Before.Operator:15:7',
            'OperatorSpacing.After.Operator:19:5',
            'OperatorSpacing.Before.Operator:19:5',
            'OperatorSpacing.After.Operator:20:5',
            'OperatorSpacing.Before.Operator:20:5',
            'OperatorSpacing.After.Operator:22:6',
            'OperatorSpacing.After.Operator:33:10',
            'OperatorSpacing.Before.Operator:33:10',
            'OperatorSpacing.After.Operator:35:13',
            'OperatorSpacing.Before.Operator:35:13',
            'OperatorSpacing.After.Operator:36:13',
            'OperatorSpacing.Before.Operator:36:13',
            'OperatorSpacing.After.Operator:37:13',
            'OperatorSpacing.Before.Operator:37:13',
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
