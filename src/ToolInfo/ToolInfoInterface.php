<?php

declare(strict_types=1);

namespace TwigCsFixer\ToolInfo;

/**
 * @internal
 */
interface ToolInfoInterface
{
    public function getComposerInstallationDetails(): array;

    public function getVersion(): string;
}
