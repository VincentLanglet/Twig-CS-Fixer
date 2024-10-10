<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

abstract class AbstractFixableRule extends AbstractRule implements FixableRuleInterface
{
    private ?FixerInterface $fixer = null;

    final protected function init(
        ?Report $report,
        array $ignoredViolations = [],
        ?FixerInterface $fixer = null,
    ): void {
        parent::init($report, $ignoredViolations);
        $this->fixer = $fixer;
    }

    final public function fixFile(Tokens $tokens, FixerInterface $fixer): void
    {
        $this->init(null, $tokens->getIgnoredViolations(), $fixer);

        foreach (array_keys($tokens->toArray()) as $index) {
            $this->process($index, $tokens);
        }
    }

    final protected function addFixableWarning(
        string $message,
        Token $token,
        ?string $messageId = null,
    ): ?FixerInterface {
        $added = $this->addWarning($message, $token, $messageId);
        if (!$added) {
            return null;
        }

        return $this->fixer;
    }

    final protected function addFixableError(
        string $message,
        Token $token,
        ?string $messageId = null,
    ): ?FixerInterface {
        $added = $this->addError($message, $token, $messageId);
        if (!$added) {
            return null;
        }

        return $this->fixer;
    }
}
