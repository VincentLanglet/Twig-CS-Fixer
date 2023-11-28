<?php

declare(strict_types=1);

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;

abstract class AbstractRule implements RuleInterface
{
    private ?Report $report = null;

    private ?FixerInterface $fixer = null;

    public function getName(): string
    {
        return static::class;
    }

    public function lintFile(array $stream, Report $report): void
    {
        $this->report = $report;
        $this->fixer = null;

        foreach (array_keys($stream) as $index) {
            $this->process($index, $stream);
        }
    }

    public function fixFile(array $stream, FixerInterface $fixer): void
    {
        $this->report = null;
        $this->fixer = $fixer;

        foreach (array_keys($stream) as $index) {
            $this->process($index, $stream);
        }
    }

    /**
     * @param array<int, Token> $tokens
     */
    abstract protected function process(int $tokenPosition, array $tokens): void;

    /**
     * @param int|string|array<int|string> $type
     * @param string|string[]              $value
     */
    protected function isTokenMatching(Token $token, int|string|array $type, string|array $value = []): bool
    {
        if (!\is_array($type)) {
            $type = [$type];
        }
        if (!\is_array($value)) {
            $value = [$value];
        }

        return \in_array($token->getType(), $type, true)
            && ([] === $value || \in_array($token->getValue(), $value, true));
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
            && $exclude === $this->isTokenMatching($tokens[$start + $i], $type)
        ) {
            $i++;
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
            && $exclude === $this->isTokenMatching($tokens[$start - $i], $type)
        ) {
            $i++;
        }

        if (!isset($tokens[$start - $i])) {
            return false;
        }

        return $start - $i;
    }

    protected function addWarning(string $message, Token $token): void
    {
        $this->addMessage(Violation::LEVEL_WARNING, $message, $token);
    }

    protected function addError(string $message, Token $token): void
    {
        $this->addMessage(Violation::LEVEL_ERROR, $message, $token);
    }

    protected function addFixableWarning(string $message, Token $token): ?FixerInterface
    {
        return $this->addFixableMessage(Violation::LEVEL_WARNING, $message, $token);
    }

    protected function addFixableError(string $message, Token $token): ?FixerInterface
    {
        return $this->addFixableMessage(Violation::LEVEL_ERROR, $message, $token);
    }

    private function addMessage(int $messageType, string $message, Token $token): void
    {
        $report = $this->report;
        if (null === $report) {
            // We are fixing the file.
            return;
        }

        $violation = new Violation(
            $messageType,
            $message,
            $token->getFilename(),
            $token->getLine(),
            $token->getPosition(),
            $this->getName(),
        );

        $report->addViolation($violation);
    }

    private function addFixableMessage(int $messageType, string $message, Token $token): ?FixerInterface
    {
        $this->addMessage($messageType, $message, $token);

        return $this->fixer;
    }
}
