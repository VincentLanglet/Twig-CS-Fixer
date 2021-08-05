<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Sniff\SniffInterface;
use TwigCsFixer\Tests\TestHelper;
use TwigCsFixer\Token\Tokenizer;

/**
 * TestCase for a Sniff.
 */
abstract class AbstractSniffTest extends TestCase
{
    /**
     * Should call $this->checkSniff(new Sniff(), [...]);
     *
     * @return void
     */
    abstract public function testSniff(): void;

    /**
     * @param SniffInterface         $sniff
     * @param array<array<int, int>> $expects
     * @param string|null            $filePath
     *
     * @return void
     */
    protected function checkSniff(SniffInterface $sniff, array $expects, ?string $filePath = null): void
    {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);
        $ruleset = new Ruleset();

        $filePath = $filePath ?? $this->generateFilePath();

        try {
            $ruleset->addSniff($sniff);
            $report = $linter->run([$filePath], $ruleset);
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }

        $fixedFile = mb_substr($filePath, 0, -5).'.fixed.twig';
        if (file_exists($fixedFile)) {
            $fixer = new Fixer($ruleset, $tokenizer);
            $sniff->enableFixer($fixer);
            $fixer->fixFile($filePath);

            $diff = TestHelper::generateDiff($fixer->getContents(), $fixedFile);
            if ('' !== $diff) {
                self::fail($diff);
            }
        }

        $messages = $report->getMessages();
        $messagePositions = [];

        foreach ($messages as $message) {
            if (Report::MESSAGE_TYPE_FATAL === $message->getLevel()) {
                $errorMessage = $message->getMessage();
                $line = $message->getLine();

                if (null !== $line) {
                    $errorMessage = sprintf('Line %s: %s', $line, $errorMessage);
                }
                self::fail($errorMessage);
            }

            $messagePositions[] = [$message->getLine() ?? 0 => $message->getLinePosition()];
        }
        self::assertSame($expects, $messagePositions);
    }

    /**
     * @return string
     */
    private function generateFilePath(): string
    {
        $class = new ReflectionClass(get_called_class());
        $className = $class->getShortName();
        $filename = $class->getFileName();
        self::assertNotFalse($filename);

        $directory = dirname($filename);

        return "$directory/$className.twig";
    }
}
