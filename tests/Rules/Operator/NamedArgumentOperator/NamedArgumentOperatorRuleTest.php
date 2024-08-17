<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Operator\NamedArgumentOperator;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use TwigCsFixer\Rules\Operator\NamedArgumentOperatorRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class NamedArgumentOperatorRuleTest extends AbstractRuleTestCase
{
    public function testRule(): void
    {
        if (!InstalledVersions::satisfies(new VersionParser(), 'twig/twig', '>=3.12.0')) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $this->checkRule(new NamedArgumentOperatorRule(), [
            'NamedArgumentOperator.Error:1:11' => 'Named arguments should be declared with the operator ":".',
        ]);
    }
}
