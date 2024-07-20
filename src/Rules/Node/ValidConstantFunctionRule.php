<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules\Node;

use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;

/**
 * Ensures constant function is used on defined constant strings.
 */
final class ValidConstantFunctionRule extends AbstractNodeRule
{
    public function enterNode(Node $node, Environment $env): Node
    {
        if (!$node instanceof FunctionExpression) {
            return $node;
        }

        $functionName = $node->getAttribute('name');
        if ('constant' !== $functionName) {
            return $node;
        }

        $arguments = $node->getNode('arguments');
        if (1 !== $arguments->count()) {
            return $node;
        }

        $argument = $arguments->getNode('0');
        if (!$argument instanceof ConstantExpression) {
            return $node;
        }

        $constant = $argument->getAttribute('value');
        if (!\is_string($constant)) {
            return $node;
        }

        if (\defined($constant)) {
            return $node;
        }

        if ('::class' === strtolower(substr($constant, -7))) {
            $this->addError(
                sprintf('You cannot use the Twig function "constant()" to access "%s". You could provide an object and call constant("class", $object) or use the class name directly as a string.', $constant),
                $node,
                'ClassConstant'
            );
        } else {
            $this->addError(
                sprintf('Constant "%s" is undefined.', $constant),
                $node,
                'ConstantUndefined'
            );
        }

        return $node;
    }
}
