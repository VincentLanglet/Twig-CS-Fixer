<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeTraverser;
use Twig\Source;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\Node\AbstractNodeRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\Rules\Node\Fixtures\FakeRule;
use TwigCsFixer\Token\Tokenizer;

final class NodeRuleTest extends TestCase
{
    public function testEnterNodeRule(): void
    {
        $report = new Report([new \SplFileInfo('fakeFile.html.twig')]);

        $rule = new class extends AbstractNodeRule {
            public function enterNode(Node $node, Environment $env): Node
            {
                $this->addWarning('Fake Warning', $node);
                $this->addError('Fake File Error', $node);

                return $node;
            }
        };
        $rule->setReport($report);

        $traverser = new NodeTraverser(new StubbedEnvironment(), [$rule]);

        $source = new Source('code', 'fakeFile.html.twig');
        $node = new Node();
        $node->setSourceContext($source);
        $traverser->traverse($node);

        static::assertSame(1, $report->getTotalWarnings());
        static::assertSame(1, $report->getTotalErrors());
    }

    public function testLeaveNodeRule(): void
    {
        $report = new Report([new \SplFileInfo('fakeFile.html.twig')]);

        $rule = new class extends AbstractNodeRule {
            public function leaveNode(Node $node, Environment $env): Node
            {
                $this->addWarning('Fake Warning', $node);
                $this->addError('Fake File Error', $node);

                return $node;
            }
        };
        $rule->setReport($report);

        $traverser = new NodeTraverser(new StubbedEnvironment(), [$rule]);

        $source = new Source('code', 'fakeFile.html.twig');
        $node = new Node();
        $node->setSourceContext($source);
        $traverser->traverse($node);

        static::assertSame(1, $report->getTotalWarnings());
        static::assertSame(1, $report->getTotalErrors());
    }

    public function testRuleName(): void
    {
        $rule = new FakeRule();
        static::assertSame(FakeRule::class, $rule->getName());
        static::assertSame('Fake', $rule->getShortName());
    }

    public function testNodeVisitorPriority(): void
    {
        $rule = new class extends AbstractNodeRule {};
        static::assertSame(0, $rule->getPriority());
    }

    /**
     * @param array<int> $expectedLines
     *
     * @dataProvider ignoredViolationsDataProvider
     */
    public function testIgnoredViolations(string $filePath, array $expectedLines): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);
        $ruleset = new Ruleset();

        $ruleset->addRule(new FakeRule());
        $report = $linter->run([new \SplFileInfo($filePath)], $ruleset);
        $messages = $report->getFileViolations($filePath);

        static::assertSame(
            $expectedLines,
            array_map(
                static fn (Violation $violation) => $violation->getLine(),
                $messages,
            ),
        );
    }

    /**
     * @return iterable<array-key, array{string, array<int>}>
     */
    public static function ignoredViolationsDataProvider(): iterable
    {
        yield [
            __DIR__.'/Fixtures/disable0.twig',
            [1],
        ];
        yield [
            __DIR__.'/Fixtures/disable1.twig',
            [],
        ];
        yield [
            __DIR__.'/Fixtures/disable2.twig',
            [2, 3, 6, 8, 9, 11, 12, 14],
        ];
    }
}
