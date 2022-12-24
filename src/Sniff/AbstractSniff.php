<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Runner\FixerInterface;
use TwigCsFixer\Token\Token;

abstract class AbstractSniff implements SniffInterface
{
    private ?Report $report = null;

    private ?FixerInterface $fixer = null;

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
     * @param int|int[]       $type
     * @param string|string[] $value
     */
    protected function isTokenMatching(Token $token, int|array $type, string|array $value = []): bool
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
     * @param int|int[]         $type
     * @param array<int, Token> $tokens
     */
    protected function findNext(int|array $type, array $tokens, int $start, bool $exclude = false): int|false
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
     * @param int|int[]         $type
     * @param array<int, Token> $tokens
     */
    protected function findPrevious(int|array $type, array $tokens, int $start, bool $exclude = false): int|false
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
        $this->addMessage(SniffViolation::LEVEL_WARNING, $message, $token);
    }

    protected function addError(string $message, Token $token): void
    {
        $this->addMessage(SniffViolation::LEVEL_ERROR, $message, $token);
    }

    protected function addFixableWarning(string $message, Token $token): ?FixerInterface
    {
        return $this->addFixableMessage(SniffViolation::LEVEL_WARNING, $message, $token);
    }

    protected function addFixableError(string $message, Token $token): ?FixerInterface
    {
        return $this->addFixableMessage(SniffViolation::LEVEL_ERROR, $message, $token);
    }

    private function addMessage(int $messageType, string $message, Token $token): void
    {
        $report = $this->report;
        if (null === $report) {
            // We are fixing the file.
            return;
        }

        $sniffViolation = new SniffViolation(
            $messageType,
            $message,
            $token->getFilename(),
            $token->getLine()
        );
        $sniffViolation->setLinePosition($token->getPosition());

        $report->addMessage($sniffViolation);
    }

    private function addFixableMessage(int $messageType, string $message, Token $token): ?FixerInterface
    {
        $this->addMessage($messageType, $message, $token);

        return $this->fixer;
    }
}
