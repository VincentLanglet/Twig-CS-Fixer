<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Token\Token;
use TwigCsFixer\Token\Tokens;

abstract class AbstractRule implements RuleInterface
{
    use RuleTrait;

    final public function lintFile(Tokens $tokens, Report $report): void
    {
        $this->init($report, $tokens->getIgnoredViolations());

        foreach (array_keys($tokens->toArray()) as $index) {
            $this->process($index, $tokens);
        }
    }

    /**
     * @param list<ViolationId> $ignoredViolations
     */
    protected function init(?Report $report, array $ignoredViolations = []): void
    {
        $this->report = $report;
        $this->ignoredViolations = $ignoredViolations;
    }

    abstract protected function process(int $tokenIndex, Tokens $tokens): void;

    final protected function addWarning(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getLinePosition(),
            $messageId,
        );
    }

    final protected function addFileWarning(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $token->getFilename(),
            null,
            null,
            $messageId,
        );
    }

    final protected function addError(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getLinePosition(),
            $messageId,
        );
    }

    final protected function addFileError(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $token->getFilename(),
            null,
            null,
            $messageId,
        );
    }
}
