<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\ForbiddenBlock;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use TwigCsFixer\Rules\Node\ForbiddenBlockRule;
use TwigCsFixer\Tests\Rules\AbstractRuleTestCase;

final class ForbiddenBlockRuleTest extends AbstractRuleTestCase
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
                'blocks' => ['foo'],
            ],
            (new ForbiddenBlockRule(['foo']))->getConfiguration()
        );
    }

    public function testRule(): void
    {
        $this->checkRule(new ForbiddenBlockRule(['trans']), [
            'ForbiddenBlock.Error:7' => 'Block "trans" is not allowed.',
        ]);
    }
}
