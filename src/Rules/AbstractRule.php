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

    public function lintFile(Tokens $tokens, Report $report, array $ignoredViolations = []): void
    {
        $this->init($report, $ignoredViolations);

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

    abstract protected function process(int $tokenPosition, Tokens $tokens): void;

    protected function addWarning(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_WARNING,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getPosition(),
            $messageId,
        );
    }

    protected function addFileWarning(string $message, Token $token, ?string $messageId = null): bool
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

    protected function addError(string $message, Token $token, ?string $messageId = null): bool
    {
        return $this->addMessage(
            Violation::LEVEL_ERROR,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getPosition(),
            $messageId,
        );
    }

    protected function addFileError(string $message, Token $token, ?string $messageId = null): bool
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
