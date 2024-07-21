<?php

declare(strict_types=1);

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\Parser;
use Twig\Token;
use Twig\TokenParser\TokenParserInterface;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Reporter\ReporterInterface;
use TwigCsFixer\Rules\AbstractRule;
use TwigCsFixer\Rules\Node\AbstractNodeRule;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\StandardInterface;
use TwigCsFixer\Token\Tokens;

$rule = new class() extends AbstractRule {
    protected function process(int $tokenIndex, Tokens $tokens): void
    {
    }
};

$nodeRule = new class() extends AbstractNodeRule {
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }
};

$standard = new class() implements StandardInterface {
    public function getRules(): array
    {
        return [];
    }
};

$reporter = new class() implements ReporterInterface {
    public function display(
        OutputInterface $output,
        Report $report,
        ?string $level,
        bool $debug
    ): void {
    }

    public function getName(): string
    {
        return 'custom';
    }
};

$twigExtension = new class() implements ExtensionInterface {
    public function getTokenParsers(): array
    {
        return [];
    }

    public function getNodeVisitors(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getTests(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [];
    }

    public function getOperators(): array
    {
        return [[], []];
    }
};

$tokenParser = new class() implements TokenParserInterface {
    public function setParser(Parser $parser): void
    {
    }

    public function parse(Token $token): Node
    {
        return new Node();
    }

    public function getTag(): string
    {
        return 'custom';
    }
};

$nodeVisitor = new class() implements NodeVisitorInterface {
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
};

$ruleset = new Ruleset();
$ruleset->addStandard($standard);
$ruleset->addRule($rule);
$ruleset->addRule($nodeRule);

$config = new Config('Custom');
$config->setFinder(new Finder());
$config->setRuleset($ruleset);
$config->addCustomReporter($reporter);
$config->addTwigExtension($twigExtension);
$config->addTokenParser($tokenParser);
$config->addNodeVisitor($nodeVisitor);

return $config;
