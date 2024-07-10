<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\OperatorSpacing;

use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class OperatorSpacingRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        $this->checkRule(new OperatorSpacingRule(), [
            'OperatorSpacing.After:1:5' => 'Expecting 1 whitespace after "+"; found 0.',
            'OperatorSpacing.Before:1:5' => 'Expecting 1 whitespace before "+"; found 0.',
            'OperatorSpacing.After:2:5' => 'Expecting 1 whitespace after "-"; found 0.',
            'OperatorSpacing.Before:2:5' => 'Expecting 1 whitespace before "-"; found 0.',
            'OperatorSpacing.After:3:5' => 'Expecting 1 whitespace after "/"; found 0.',
            'OperatorSpacing.Before:3:5' => 'Expecting 1 whitespace before "/"; found 0.',
            'OperatorSpacing.After:4:5' => 'Expecting 1 whitespace after "*"; found 0.',
            'OperatorSpacing.Before:4:5' => 'Expecting 1 whitespace before "*"; found 0.',
            'OperatorSpacing.After:5:5' => 'Expecting 1 whitespace after "%"; found 0.',
            'OperatorSpacing.Before:5:5' => 'Expecting 1 whitespace before "%"; found 0.',
            'OperatorSpacing.After:6:5' => 'Expecting 1 whitespace after "//"; found 0.',
            'OperatorSpacing.Before:6:5' => 'Expecting 1 whitespace before "//"; found 0.',
            'OperatorSpacing.After:7:5' => 'Expecting 1 whitespace after "**"; found 0.',
            'OperatorSpacing.Before:7:5' => 'Expecting 1 whitespace before "**"; found 0.',
            'OperatorSpacing.After:8:7' => 'Expecting 1 whitespace after "~"; found 0.',
            'OperatorSpacing.Before:8:7' => 'Expecting 1 whitespace before "~"; found 0.',
            'OperatorSpacing.After:9:10' => 'Expecting 1 whitespace after "?"; found 2.',
            'OperatorSpacing.Before:9:10' => 'Expecting 1 whitespace before "?"; found 2.',
            'OperatorSpacing.After:9:19' => 'Expecting 1 whitespace after ":"; found 2.',
            'OperatorSpacing.Before:9:19' => 'Expecting 1 whitespace before ":"; found 2.',
            'OperatorSpacing.After:10:5' => 'Expecting 1 whitespace after "=="; found 0.',
            'OperatorSpacing.Before:10:5' => 'Expecting 1 whitespace before "=="; found 0.',
            'OperatorSpacing.After:11:4' => 'Expecting 1 whitespace after "not"; found 3.',
            'OperatorSpacing.After:12:11' => 'Expecting 1 whitespace after "and"; found 3.',
            'OperatorSpacing.Before:12:11' => 'Expecting 1 whitespace before "and"; found 2.',
            'OperatorSpacing.After:13:11' => 'Expecting 1 whitespace after "or"; found 3.',
            'OperatorSpacing.Before:13:11' => 'Expecting 1 whitespace before "or"; found 2.',
            'OperatorSpacing.After:14:7' => 'Expecting 1 whitespace after "in"; found 3.',
            'OperatorSpacing.Before:14:7' => 'Expecting 1 whitespace before "in"; found 2.',
            'OperatorSpacing.After:15:7' => 'Expecting 1 whitespace after "is"; found 3.',
            'OperatorSpacing.Before:15:7' => 'Expecting 1 whitespace before "is"; found 2.',
            'OperatorSpacing.After:19:5' => 'Expecting 1 whitespace after "?:"; found 0.',
            'OperatorSpacing.Before:19:5' => 'Expecting 1 whitespace before "?:"; found 0.',
            'OperatorSpacing.After:20:5' => 'Expecting 1 whitespace after "??"; found 0.',
            'OperatorSpacing.Before:20:5' => 'Expecting 1 whitespace before "??"; found 0.',
            'OperatorSpacing.After:22:6' => 'Expecting 1 whitespace after "+"; found 0.',
            'OperatorSpacing.After:33:10' => 'Expecting 1 whitespace after "+"; found 0.',
            'OperatorSpacing.Before:33:10' => 'Expecting 1 whitespace before "+"; found 0.',
            'OperatorSpacing.After:35:13' => 'Expecting 1 whitespace after "starts with"; found 2.',
            'OperatorSpacing.Before:35:13' => 'Expecting 1 whitespace before "starts with"; found 2.',
            'OperatorSpacing.After:36:13' => 'Expecting 1 whitespace after "ends with"; found 2.',
            'OperatorSpacing.Before:36:13' => 'Expecting 1 whitespace before "ends with"; found 2.',
            'OperatorSpacing.After:37:13' => 'Expecting 1 whitespace after "matches"; found 2.',
            'OperatorSpacing.Before:37:13' => 'Expecting 1 whitespace before "matches"; found 2.',
        ]);
    }

    public function testRuleWithTab(): void
    {
        $this->checkRule(
            new OperatorSpacingRule(),
            [],
            __DIR__.'/OperatorSpacingRuleTest.tab.twig'
        );
    }
}
