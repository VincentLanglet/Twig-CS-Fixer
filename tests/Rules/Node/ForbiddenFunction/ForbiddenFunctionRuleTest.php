<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ForbiddenFunction;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use TwigCsFixer\Rules\Node\ForbiddenFunctionRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ForbiddenFunctionRuleTest extends AbstractRuleTestCase
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
                'functions' => ['foo'],
            ],
            (new ForbiddenFunctionRule(['foo']))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new ForbiddenFunctionRule(['trans']), [
            'ForbiddenFunction.Error:8' => 'Function "trans" is not allowed.',
        ]);
    }
}
