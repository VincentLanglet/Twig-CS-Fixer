<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

final class ViolationId
{
    public function __construct(
        private ?string $ruleIdentifier = null,
        private ?string $messageIdentifier = null,
        private ?int $line = null,
        private ?int $linePosition = null,
    ) {
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getLinePosition(): ?int
    {
        return $this->linePosition;
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
            $linePosition
        );
    }

    public function toString(): string
    {
        $name = rtrim(\sprintf(
            '%s.%s',
            $this->ruleIdentifier ?? '',
            $this->messageIdentifier ?? '',
        ), '.');

        return rtrim(\sprintf(
            '%s:%s:%s',
            $name,
            $this->line ?? '',
            $this->linePosition ?? '',
        ), ':');
    }

    public function match(self $violationId): bool
    {
        return $this->matchValue($this->ruleIdentifier, $violationId->ruleIdentifier)
            && $this->matchValue($this->messageIdentifier, $violationId->messageIdentifier)
            && $this->matchValue($this->line, $violationId->line)
            && $this->matchValue($this->linePosition, $violationId->linePosition);
    }

    private function matchValue(string|int|null $self, string|int|null $other): bool
    {
        return null === $self || strtolower((string) $self) === strtolower((string) $other);
    }
}
