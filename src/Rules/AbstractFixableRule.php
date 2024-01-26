<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;

abstract class AbstractFixableRule extends AbstractRule implements FixableRuleInterface
{
    private ?FixerInterface $fixer = null;

    protected function init(
        ?Report $report,
        array $ignoredViolations = [],
        ?FixerInterface $fixer = null
    ): void {
        parent::init($report, $ignoredViolations);
        $this->fixer = $fixer;
    }

    public function fixFile(array $stream, FixerInterface $fixer, array $ignoredViolations = []): void
    {
        $this->init(null, $ignoredViolations, $fixer);

        foreach (array_keys($stream) as $index) {
            $this->process($index, $stream);
        }
    }

    protected function addFixableWarning(
        string $message,
        Token $token,
        ?string $messageId = null
    ): ?FixerInterface {
        $added = $this->addWarning($message, $token, $messageId);
        if (!$added) {
            return null;
        }

        return $this->fixer;
    }

    protected function addFixableError(
        string $message,
        Token $token,
        ?string $messageId = null
    ): ?FixerInterface {
        $added = $this->addError($message, $token, $messageId);
        if (!$added) {
            return null;
        }

        return $this->fixer;
    }
}
