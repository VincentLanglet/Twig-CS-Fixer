<?php

declare(strict_types=1);

namespace TwigCsFixer\Test;

use PHPUnit\Framework\TestCase;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Rules\Node\NodeRuleInterface;
use TwigCsFixer\Rules\RuleInterface;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Token\Tokenizer;

abstract class AbstractRuleTestCase extends TestCase
{
    /**
     * @param RuleInterface|NodeRuleInterface|array<RuleInterface|NodeRuleInterface> $rules
     * @param array<string|null>                                                     $expects
     *
     * @throws CannotFixFileException
     * @throws CannotTokenizeException
     */
    protected function checkRule(
        RuleInterface|NodeRuleInterface|array $rules,
        array $expects,
        ?string $filePath = null,
        string|false|null $fixedFilePath = null,
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

        if (false !== $fixedFilePath) {
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
        }

        $violations = $report->getFileViolations($filePath);

        /** @var array<string, string> $messages */
        $messages = [];
        foreach ($violations as $violation) {
            $message = $violation->getMessage();
            if (Violation::LEVEL_FATAL === $violation->getLevel()) {
                $line = $violation->getLine();

                if (null !== $line) {
                    $message = \sprintf('Line %s: %s', $line, $message);
                }
                static::fail($message);
            }

            $id = $violation->getIdentifier()?->toString() ?? '';
            if (isset($messages[$id])) {
                static::fail(\sprintf(
                    'Two violations have the same identifier "%s": the messages are "%s" and "%s".',
                    $id,
                    $messages[$id],
                    $message,
                ));
            }

            $messages[$id] = $message;
        }

        static::assertSame($expects, $messages);
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
