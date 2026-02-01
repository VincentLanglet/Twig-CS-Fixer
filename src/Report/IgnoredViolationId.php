<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

final class IgnoredViolationId
{
    public function __construct(
        private ?string $ruleIdentifier = null,
        private ?string $messageIdentifier = null,
        private ?int $startLine = null,
        private ?int $startLinePosition = null,
        private ?int $endLine = null,
        private ?int $endLinePosition = null,
    ) {
    }

    public static function fromString(string $string, ?int $line = null): self
    {
        $exploded = explode(':', $string);
        $name = $exploded[0];
        $explodedName = '' !== $name ? explode('.', $name) : null;

        $line ??= isset($exploded[1]) && '' !== $exploded[1] ? (int) $exploded[1] : null;
        $linePosition = isset($exploded[2]) && '' !== $exploded[2] ? (int) $exploded[2] : null;

        return new self(
            $explodedName[0] ?? null,
            $explodedName[1] ?? null,
            $line,
            $linePosition,
            $line,
            $linePosition
        );
    }

    public function match(ViolationId $violationId): bool
    {
        return $this->matchIdentifier($this->ruleIdentifier, $violationId->getRuleIdentifier())
            && $this->matchIdentifier($this->messageIdentifier, $violationId->getMessageIdentifier())
            && $this->matchLinePosition($violationId->getLine(), $violationId->getLinePosition());
    }

    private function matchIdentifier(?string $self, ?string $other): bool
    {
        return null === $self || strtolower($self) === strtolower((string) $other);
    }

    private function matchLinePosition(?int $line, ?int $linePosition): bool
    {
        if (null !== $this->startLine) {
            if (null === $line || $line < $this->startLine) {
                return false;
            }
            if (
                $line === $this->startLine
                && null !== $this->startLinePosition
                && (null === $linePosition || $linePosition < $this->startLinePosition)
            ) {
                return false;
            }
        } elseif (
            null !== $this->startLinePosition
            && (null === $linePosition || $linePosition < $this->startLinePosition)
        ) {
            return false;
        }

        if (null !== $this->endLine) {
            if (null === $line || $line > $this->endLine) {
                return false;
            }
            if (
                $line === $this->endLine
                && null !== $this->endLinePosition
                && (null === $linePosition || $linePosition > $this->endLinePosition)
            ) {
                return false;
            }
        } elseif (
            null !== $this->endLinePosition
            && (null === $linePosition || $linePosition > $this->endLinePosition)
        ) {
            return false;
        }

        return true;
    }
}
