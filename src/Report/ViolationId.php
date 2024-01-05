<?php

declare(strict_types=1);

namespace TwigCsFixer\Report;

final class ViolationId
{
    public function __construct(
        private string $ruleShortName,
        private ?string $identifier = null,
        private ?string $tokenName = null,
        private ?int $line = null,
        private ?int $linePosition = null,
    ) {
    }

    public static function fromString(string $string, ?int $line = null): self
    {
        $exploded = explode(':', $string);
        $name = $exploded[0];
        $explodedName = explode('.', $name);

        $line ??= isset($exploded[1]) ? (int) $exploded[1] : null;
        $position = isset($exploded[2]) ? (int) $exploded[2] : null;

        return new self(
            $explodedName[0],
            $explodedName[1] ?? null,
            $explodedName[2] ?? null,
            $line,
            $position
        );
    }

    public function toString(): string
    {
        $name = rtrim(sprintf(
            '%s.%s.%s',
            $this->ruleShortName,
            $this->identifier ?? '',
            $this->tokenName ?? ''
        ), '.');

        return rtrim(sprintf(
            '%s:%s:%s',
            $name,
            $this->line ?? '',
            $this->linePosition ?? '',
        ), ':');
    }

    public function match(self $violationId): bool
    {
        return $this->ruleShortName === $violationId->ruleShortName
            && (null === $this->identifier || $this->identifier === $violationId->identifier)
            && (null === $this->tokenName || $this->tokenName === $violationId->tokenName)
            && (null === $this->line || $this->line === $violationId->line)
            && (null === $this->linePosition || $this->linePosition === $violationId->linePosition);
    }
}
