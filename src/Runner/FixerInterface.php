<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Ruleset\Ruleset;

interface FixerInterface
{
    /**
     * @throws CannotTokenizeException
     * @throws CannotFixFileException
     */
    public function fixFile(string $content, Ruleset $ruleset): string;

    /**
     * Start recording actions for a changeset.
     */
    public function beginChangeset(): void;

    /**
     * Stop recording actions for a changeset, and apply logged changes.
     */
    public function endChangeset(): void;

    public function replaceToken(int $tokenPosition, string $content): bool;

    public function addNewline(int $tokenPosition): bool;

    public function addNewlineBefore(int $tokenPosition): bool;

    public function addContent(int $tokenPosition, string $content): bool;

    public function addContentBefore(int $tokenPosition, string $content): bool;
}
