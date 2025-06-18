<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\NodeVisitor\NodeVisitorInterface;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\ViolationId;

interface PathAwareNodeRuleInterface extends NodeRuleInterface
{
    public function support(string $path): bool;
}
