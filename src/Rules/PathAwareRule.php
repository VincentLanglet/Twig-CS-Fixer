<?php

namespace TwigCsFixer\Rules;

use TwigCsFixer\Report\Report;
use TwigCsFixer\Token\Tokens;

final class PathAwareRule implements PathAwareRuleInterface, ConfigurableRuleInterface
{
    public function __construct(
        private RuleInterface $rule,
        private string $filePattern,
    ) {
    }

    public function getConfiguration(): array
    {
        $config = $this->rule instanceof ConfigurableRuleInterface ? $this->rule->getConfiguration() : [];
        $config['__class'] = $this->rule::class;
        $config['__file_pattern'] = $this->filePattern;

        return $config;
    }

    public function lintFile(Tokens $tokens, Report $report): void
    {
        $this->rule->lintFile($tokens, $report);
    }

    public function support(string $path): bool
    {
        return fnmatch($this->filePattern, $path);
    }
}
