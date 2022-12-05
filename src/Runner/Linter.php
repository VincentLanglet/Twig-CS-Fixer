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
    public function run(iterable $files, Ruleset $ruleset, ?Fixer $fixer = null): Report
    {
        $report = new Report($files);

        // Process
        foreach ($files as $file) {
            $filePath = $file->getPathname();

            $content = @file_get_contents($filePath);
            if (false === $content) {
                $sniffViolation = new SniffViolation(
                    SniffViolation::LEVEL_FATAL,
                    'Unable to read file.',
                    $filePath
                );

                $report->addMessage($sniffViolation);
                continue;
            }

            if (!$this->cacheManager->needFixing($filePath, $content)) {
                continue;
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

                continue;
            }

            // Tokenizer.
            $this->setErrorHandler($report, $filePath);
            try {
                $stream = $this->tokenizer->tokenize($twigSource);
            } catch (CannotTokenizeException $exception) {
                $sniffViolation = new SniffViolation(
                    SniffViolation::LEVEL_FATAL,
                    sprintf('Unable to tokenize file: %s', $exception->getMessage()),
                    $filePath
                );

                $report->addMessage($sniffViolation);

                continue;
            }
            restore_error_handler();

            if (null !== $fixer) {
                try {
                    $content = $fixer->fixFile($content, $ruleset);
                    file_put_contents($filePath, $content);
                } catch (CannotFixFileException|CannotTokenizeException $exception) {
                    $sniffViolation = new SniffViolation(
                        SniffViolation::LEVEL_FATAL,
                        sprintf('Unable to fix file: %s', $exception->getMessage()),
                        $filePath
                    );

                    $report->addMessage($sniffViolation);
                }
            }

            $sniffs = $ruleset->getSniffs();
            foreach ($sniffs as $sniff) {
                $sniff->lintFile($stream, $report);
            }

            // TODO: Add the ability to cache result for files with errors
            if ([] === $report->getMessages($filePath)) {
                $this->cacheManager->setFile($filePath, $content);
            }
        }

        return $report;
    }

    private function setErrorHandler(Report $report, string $file): void
    {
        set_error_handler(static function (int $type, string $message) use ($report, $file): bool {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_NOTICE,
                $message,
                $file
            );

            $report->addMessage($sniffViolation);

            return true;
        }, \E_USER_DEPRECATED);
    }
}
