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
use TwigCsFixer\Report\SniffViolation;
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
    public function run(iterable $files, Ruleset $ruleset, bool $fix): Report
    {
        $report = new Report($files);

        if ($fix) {
            $this->fix($files, $ruleset, $report);
        }

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableReport($report);
        }

        // Process
        foreach ($files as $file) {
            $filePath = $file->getPathname();

            $fileContent = @file_get_contents($filePath);
            if (
                false !== $fileContent
                && !$this->cacheManager->needFixing($filePath, $fileContent)
            ) {
                continue;
            }

            $this->setErrorHandler($report, $filePath);
            $this->processTemplate($filePath, $ruleset, $report);
        }
        restore_error_handler();

        // tearDown
        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->disable();
        }

        return $report;
    }

    /**
     * @param iterable<SplFileInfo> $files
     */
    private function fix(iterable $files, Ruleset $ruleset, Report $report): void
    {
        $fixer = new Fixer($ruleset, $this->tokenizer);

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableFixer($fixer);
        }

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $contents = @file_get_contents($filePath);
            if (false !== $contents && !$this->cacheManager->needFixing($filePath, $contents)) {
                continue;
            }

            try {
                $fixer->fixFile($filePath);
            } catch (CannotFixFileException|CannotTokenizeException $exception) {
                $sniffViolation = new SniffViolation(
                    SniffViolation::LEVEL_FATAL,
                    sprintf('Unable to fix file: %s', $exception->getMessage()),
                    $filePath
                );

                $report->addMessage($sniffViolation);
            }

            $contents = $fixer->getContents();
            file_put_contents($filePath, $contents);
            $this->cacheManager->setFile($filePath, $contents);
        }
    }

    private function processTemplate(string $filePath, Ruleset $ruleset, Report $report): void
    {
        $content = @file_get_contents($filePath);
        if (false === $content) {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_FATAL,
                'Unable to read file.',
                $filePath
            );

            $report->addMessage($sniffViolation);

            return;
        }

        $twigSource = new Source($content, $filePath);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (Error $error) {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_FATAL,
                sprintf('File is invalid: %s', $error->getRawMessage()),
                $filePath,
                $error->getTemplateLine()
            );

            $report->addMessage($sniffViolation);

            return;
        }

        // Tokenizer.
        try {
            $stream = $this->tokenizer->tokenize($twigSource);
        } catch (CannotTokenizeException $exception) {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_FATAL,
                sprintf('Unable to tokenize file: %s', $exception->getMessage()),
                $filePath
            );

            $report->addMessage($sniffViolation);

            return;
        }

        $sniffs = $ruleset->getSniffs();
        foreach ($sniffs as $sniff) {
            $sniff->processFile($stream);
        }
    }

    private function setErrorHandler(Report $report, string $file): void
    {
        set_error_handler(static function (int $type, string $message) use ($report, $file): bool {
            if (\E_USER_DEPRECATED === $type) {
                $sniffViolation = new SniffViolation(
                    SniffViolation::LEVEL_NOTICE,
                    $message,
                    $file
                );

                $report->addMessage($sniffViolation);

                return true;
            }

            return false;
        });
    }
}
