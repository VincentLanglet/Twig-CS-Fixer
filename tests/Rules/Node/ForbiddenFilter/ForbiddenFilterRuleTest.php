<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ForbiddenFilter;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use TwigCsFixer\Rules\Node\ForbiddenFilterRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ForbiddenFilterRuleTest extends AbstractRuleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!InstalledVersions::satisfies(new VersionParser(), 'twig/twig', '>=3.10.0')) {
            static::markTestSkipped('twig/twig ^3.10.0 is required.');
        }
    }

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
