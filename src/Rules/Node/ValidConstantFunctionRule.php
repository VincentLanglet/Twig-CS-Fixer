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
        if ($arguments->hasNode('0')) {
            $argument = $arguments->getNode('0');
        } elseif ($arguments->hasNode('constant')) {
            // Try for named parameters
            $argument = $arguments->getNode('constant');
        } else {
            $this->addError(
                'The first param of the function "constant()" is required.',
                $node,
                'NoConstant'
            );

            return $node;
        }
        if (!$argument instanceof ConstantExpression) {
            return $node;
        }

        $constant = $argument->getAttribute('value');
        if (!\is_string($constant)) {
            $this->addError(
                'The first param of the function "constant()" must be a string.',
                $node,
                'StringConstant'
            );

            return $node;
        }

        // The object to get the constant from cannot be resolved statically.
        if (1 !== $arguments->count()) {
            return $node;
        }

        if (\defined($constant)) {
            return $node;
        }

        if ('::class' === strtolower(substr($constant, -7))) {
            $this->addError(
                'You cannot use the function "constant()" to resolve class names.',
                $node,
                'ClassConstant'
            );
        } else {
            $this->addError(
                \sprintf('Constant "%s" is undefined.', $constant),
                $node,
                'ConstantUndefined'
            );
        }

        return $node;
    }
}
