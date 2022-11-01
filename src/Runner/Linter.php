<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use Exception;
use RuntimeException;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use TwigCsFixer\Cache\CacheManagerInterface;
use TwigCsFixer\Cache\NullCacheManager;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\TokenizerInterface;

/**
 * Linter is the main class and will process twig files against a set of rules.
 */
final class Linter
{
    private Environment $env;

    private TokenizerInterface $tokenizer;

    private CacheManagerInterface $cacheManager;

    public function __construct(Environment $env, TokenizerInterface $tokenizer, ?CacheManagerInterface $cacheManager = null)
    {
        $this->env = $env;
        $this->tokenizer = $tokenizer;
        $this->cacheManager = $cacheManager ?? new NullCacheManager();
    }

    /**
     * @param iterable<SplFileInfo> $files
     *
     * @throws RuntimeException
     */
    public function run(iterable $files, Ruleset $ruleset, bool $fix): Report
    {
        $report = new Report();

        if ($fix) {
            $this->fix($files, $ruleset);
        }

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableReport($report);
        }

        // Process
        foreach ($files as $file) {
            $filePath = $file->getPathname();

            // Add this file to the report.
            $report->addFile($filePath);

            $fileContent = file_get_contents($filePath);
            if (false === $fileContent || $this->cacheManager->needFixing($filePath, $fileContent)) {
                $this->setErrorHandler($report, $filePath);
                $this->processTemplate($filePath, $ruleset, $report);
            }
        }
        restore_error_handler();

        // tearDown
        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->disable();
        }

        return $report;
    }

    /**
     * @param iterable<SplFileInfo> $finder
     *
     * @throws RuntimeException
     */
    private function fix(iterable $finder, Ruleset $ruleset): void
    {
        $fixer = new Fixer($ruleset, $this->tokenizer);

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableFixer($fixer);
        }

        foreach ($finder as $file) {
            $filePath = $file->getPathname();
            $contents = file_get_contents($filePath);
            if (false === $contents) {
                throw new RuntimeException(sprintf('Cannot fix file "%s".', $filePath));
            }

            if (!$this->cacheManager->needFixing($filePath, $contents)) {
                continue;
            }

            if (!$fixer->fixFile($filePath)) {
                throw new RuntimeException(sprintf('Cannot fix file "%s".', $filePath));
            }

            $contents = $fixer->getContents();
            file_put_contents($filePath, $contents);
            $this->cacheManager->setFile($filePath, $contents);
        }
    }

    private function processTemplate(string $file, Ruleset $ruleset, Report $report): void
    {
        $content = file_get_contents($file);
        if (false === $content) {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_FATAL,
                'Unable to read file.',
                $file
            );

            $report->addMessage($sniffViolation);

            return;
        }

        $twigSource = new Source($content, $file);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (Error $e) {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_FATAL,
                sprintf('File is invalid: %s', $e->getRawMessage()),
                $file,
                $e->getTemplateLine()
            );

            $report->addMessage($sniffViolation);

            return;
        }

        // Tokenizer.
        try {
            $stream = $this->tokenizer->tokenize($twigSource);
        } catch (Exception $exception) {
            $sniffViolation = new SniffViolation(
                SniffViolation::LEVEL_FATAL,
                sprintf('Unable to tokenize file: %s', $exception->getMessage()),
                $file
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
