<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Cache;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Cache\Signature;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\ConfigurableRuleInterface;
use TwigCsFixer\Rules\Operator\OperatorSpacingRule;
use TwigCsFixer\Rules\RuleInterface;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\Tokens;

final class SignatureTest extends TestCase
{
    public function testSignature(): void
    {
        $signature = new Signature('8.0', '1', [OperatorSpacingRule::class => null]);

        static::assertSame('8.0', $signature->getPhpVersion());
        static::assertSame('1', $signature->getFixerVersion());
        static::assertSame([OperatorSpacingRule::class => null], $signature->getRules());
    }

    public function testSignatureFromRuleset(): void
    {
        $ruleset = new Ruleset();

        $rule = static::createStub(RuleInterface::class);
        $ruleset->addRule($rule);

        $configurableRule = new class extends AbstractRule implements ConfigurableRuleInterface {
            public function getConfiguration(): array
            {
                return ['a' => 1];
            }

            protected function process(int $tokenIndex, Tokens $tokens): void
            {
            }
        };
        $ruleset->addRule($configurableRule);

        $signature = Signature::fromRuleset('8.0', '1', $ruleset);

        static::assertSame('8.0', $signature->getPhpVersion());
        static::assertSame('1', $signature->getFixerVersion());
        static::assertSame(
            [
                $rule::class => null,
                $configurableRule::class => ['a' => 1],
            ],
            $signature->getRules()
        );
    }

    /**
     * @dataProvider equalsDataProvider
     */
    public function testEquals(Signature $signature1, Signature $signature2, bool $expected): void
    {
        static::assertSame($expected, $signature1->equals($signature2));
        static::assertSame($expected, $signature2->equals($signature1));
    }

    /**
     * @return iterable<array-key, array{Signature, Signature, bool}>
     */
    public static function equalsDataProvider(): iterable
    {
        $signature1 = new Signature('8.0', '1', [OperatorSpacingRule::class => null]);
        $signature2 = new Signature('8.0', '1', [OperatorSpacingRule::class => null]);
        $signature3 = new Signature('8.1', '1', [OperatorSpacingRule::class => null]);
        $signature4 = new Signature('8.0', '2', [OperatorSpacingRule::class => null]);
        $signature5 = new Signature('8.0', '1', []);

        yield [$signature1, $signature1, true];
        yield [$signature1, $signature2, true];
        yield [$signature1, $signature3, false];
        yield [$signature1, $signature4, false];
        yield [$signature1, $signature5, false];
    }
}
