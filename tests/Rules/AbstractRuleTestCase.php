<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Rules;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\RuleInterface;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Tests\TestHelper;
use TwigCsFixer\Token\Tokenizer;

abstract class AbstractRuleTestCase extends TestCase
{
    /**
     * @param RuleInterface|array<RuleInterface> $rules
     * @param array<string|null>                 $expects
     */
    protected function checkRule(
        RuleInterface|array $rules,
        array $expects,
        ?string $filePath = null,
        ?string $fixedFilePath = null,
    ): void {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);
        $ruleset = new Ruleset();

        $filePath ??= $this->generateFilePath();

        if (\is_array($rules)) {
            foreach ($rules as $rule) {
                $ruleset->addRule($rule);
            }
        } else {
            $ruleset->addRule($rules);
        }

        $report = $linter->run([new \SplFileInfo($filePath)], $ruleset);

        $fixedFilePath ??= substr($filePath, 0, -5).'.fixed.twig';
        if (file_exists($fixedFilePath)) {
            $content = file_get_contents($filePath);
            static::assertNotFalse($content);
            $fixer = new Fixer($tokenizer);

            $diff = TestHelper::generateDiff($fixer->fixFile($content, $ruleset), $fixedFilePath);
            if ('' !== $diff) {
                static::fail($diff);
            }
        }

        $messages = $report->getFileViolations($filePath);

        /** @var array<string|null> $messageIds */
        $messageIds = [];
        foreach ($messages as $message) {
            if (Violation::LEVEL_FATAL === $message->getLevel()) {
                $errorMessage = $message->getMessage();
                $line = $message->getLine();

                if (null !== $line) {
                    $errorMessage = sprintf('Line %s: %s', $line, $errorMessage);
                }
                static::fail($errorMessage);
            }

            $messageIds[] = $message->getIdentifier()?->toString();
        }

        static::assertSame($expects, $messageIds);
    }

    private function generateFilePath(): string
    {
        $class = new \ReflectionClass(static::class);
        $className = $class->getShortName();
        $filename = $class->getFileName();
        static::assertNotFalse($filename);

        $directory = \dirname($filename);

        return "{$directory}/{$className}.twig";
    }
}
