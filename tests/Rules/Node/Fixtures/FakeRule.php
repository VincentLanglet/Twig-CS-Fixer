<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules\Node\Fixtures;

use Twig\Environment;
use Twig\Node\Node;
use TwigCsFixer\Rules\Node\AbstractNodeRule;

/**
 * This rule reports an error for the first token of every line.
 */
final class FakeRule extends AbstractNodeRule
{
    /**
     * @var array<int, bool>
     */
    private array $errorByLine = [];

    public function enterNode(Node $node, Environment $env): Node
    {
        if (0 !== $node->getTemplateLine() && !isset($this->errorByLine[$node->getTemplateLine()])) {
            $this->errorByLine[$node->getTemplateLine()] = true;

            $this->addError('First node of the line', $node);
        }

        return $node;
    }
}
