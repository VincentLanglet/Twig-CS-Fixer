<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use SplFileInfo;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use TwigCsFixer\Cache\Manager\CacheManagerInterface;
use TwigCsFixer\Cache\Manager\NullCacheManager;
use TwigCsFixer\Exception\CannotFixFileException;
use TwigCsFixer\Exception\CannotTokenizeException;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\Violation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\TokenizerInterface;

/**
 * Linter is the main class and will process twig files against a set of rules.
 */
final class Linter
{
    private CacheManagerInterface $cacheManager;

    public function __construct(
        private Environment $env,
        private TokenizerInterface $tokenizer,
        ?CacheManagerInterface $cacheManager = null
    ) {
        $this->cacheManager = $cacheManager ?? new NullCacheManager();
    }

    /**
     * @param iterable<SplFileInfo> $files
     */
    public function run(iterable $files, Ruleset $ruleset, ?FixerInterface $fixer = null): Report
    {
        $report = new Report($files);

        // Process
        foreach ($files as $file) {
            $filePath = $file->getPathname();

            $content = @file_get_contents($filePath);
            if (false === $content) {
                $violation = new Violation(
                    Violation::LEVEL_FATAL,
                    'Unable to read file.',
                    $filePath
                );

                $report->addViolation($violation);
                continue;
            }

            if (!$this->cacheManager->needFixing($filePath, $content)) {
                continue;
            }

            // Validate the file with twig parser.
            try {
                $twigSource = new Source($content, $filePath);
                $this->env->parse($this->env->tokenize($twigSource));
            } catch (Error $error) {
                $violation = new Violation(
                    Violation::LEVEL_FATAL,
                    sprintf('File is invalid: %s', $error->getRawMessage()),
                    $filePath,
                    $error->getTemplateLine()
                );

                $report->addViolation($violation);
                continue;
            }

            if (null !== $fixer) {
                try {
                    $newContent = $fixer->fixFile($content, $ruleset);
                    // Don't write the file if it is unchanged in order not to invalidate mtime based caches.
                    if ($newContent !== $content) {
                        file_put_contents($filePath, $newContent);
                        $content = $newContent;
                        $report->addFixedFile($filePath);
                    }
                } catch (CannotTokenizeException $exception) {
                    $violation = new Violation(
                        Violation::LEVEL_FATAL,
                        sprintf('Unable to tokenize file: %s', $exception->getMessage()),
                        $filePath
                    );

                    $report->addViolation($violation);
                    continue;
                } catch (CannotFixFileException $exception) {
                    $violation = new Violation(
                        Violation::LEVEL_FATAL,
                        sprintf('Unable to fix file: %s', $exception->getMessage()),
                        $filePath
                    );

                    $report->addViolation($violation);
                }
            }

            // Tokenize file in order to lint.
            $this->setErrorHandler($report, $filePath);
            try {
                $twigSource = new Source($content, $filePath);
                $stream = $this->tokenizer->tokenize($twigSource);
            } catch (CannotTokenizeException $exception) {
                $violation = new Violation(
                    Violation::LEVEL_FATAL,
                    sprintf('Unable to tokenize file: %s', $exception->getMessage()),
                    $filePath
                );

                $report->addViolation($violation);
                continue;
            }
            restore_error_handler();

            $rules = $ruleset->getRules();
            foreach ($rules as $rule) {
                $rule->lintFile($stream, $report);
            }

            // Only cache the file if there is no error in order to
            // - still see the errors when running again the linter
            // - still having the possibility to fix the file
            if ([] === $report->getFileViolations($filePath)) {
                $this->cacheManager->setFile($filePath, $content);
            }
        }

        return $report;
    }

    private function setErrorHandler(Report $report, string $file): void
    {
        set_error_handler(static function (int $type, string $message) use ($report, $file): bool {
            $violation = new Violation(
                Violation::LEVEL_NOTICE,
                $message,
                $file
            );

            $report->addViolation($violation);

            return true;
        }, \E_USER_DEPRECATED);
    }
}
