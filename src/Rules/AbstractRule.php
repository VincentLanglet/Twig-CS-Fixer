<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Report\ViolationId;
use TwigCsFixer\Token\Token;

abstract class AbstractRule implements RuleInterface
{
    use RuleTrait;

    public function lintFile(array $stream, Report $report, array $ignoredViolations = []): void
    {
        $this->init($report, $ignoredViolations);

        foreach (array_keys($stream) as $index) {
            $this->process($index, $stream);
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

    /**
     * @param array<int, Token> $tokens
     */
    abstract protected function process(int $tokenPosition, array $tokens): void;

    /**
     * @param int|string|array<int|string> $type
     * @param string|string[]              $value
     *
     * @deprecated use Token::isMatching() instead
     */
    protected function isTokenMatching(Token $token, int|string|array $type, string|array $value = []): bool
    {
        return $token->isMatching($type, $value);
    }

    /**
     * @param int|string|array<int|string> $type
     * @param array<int, Token>            $tokens
     */
    protected function findNext(int|string|array $type, array $tokens, int $start, bool $exclude = false): int|false
    {
        $i = 0;

        while (
            isset($tokens[$start + $i])
            && $exclude === $tokens[$start + $i]->isMatching($tokens[$start + $i], $type)
        ) {
            ++$i;
        }

        if (!isset($tokens[$start + $i])) {
            return false;
        }

        return $start + $i;
    }

    /**
     * @param int|string|array<int|string> $type
     * @param array<int, Token>            $tokens
     */
    protected function findPrevious(int|string|array $type, array $tokens, int $start, bool $exclude = false): int|false
    {
        $i = 0;

        while (
            isset($tokens[$start - $i])
            && $exclude === $tokens[$start - $i]->isMatching($tokens[$start - $i], $type)
        ) {
            ++$i;
        }

        if (!isset($tokens[$start - $i])) {
            return false;
        }

        return $start - $i;
    }

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
