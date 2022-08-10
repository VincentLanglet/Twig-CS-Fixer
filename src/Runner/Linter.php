<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use Exception;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\TokenizerInterface;

use function file_get_contents;
use function file_put_contents;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;

/**
 * Linter is the main class and will process twig files against a set of rules.
 */
final class Linter
{
    /**
     * @var Environment
     */
    private Environment $env;

    /**
     * @var TokenizerInterface
     */
    private TokenizerInterface $tokenizer;

    /**
     * @param Environment        $env
     * @param TokenizerInterface $tokenizer
     *
     * @return void
     */
    public function __construct(Environment $env, TokenizerInterface $tokenizer)
    {
        $this->env = $env;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @param iterable<SplFileInfo> $files
     * @param Ruleset               $ruleset
     * @param bool                  $fix
     *
     * @return Report
     *
     * @throws Exception
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
     * @param iterable<SplFileInfo> $finder
     * @param Ruleset               $ruleset
     *
     * @return void
     *
     * @throws Exception
     */
    private function fix(iterable $finder, Ruleset $ruleset): void
    {
        $fixer = new Fixer($ruleset, $this->tokenizer);

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableFixer($fixer);
        }

        foreach ($finder as $file) {
            $filePath = $file->getPathname();
            $success = $fixer->fixFile($filePath);

            if (!$success) {
                throw new Exception(sprintf('Cannot fix the file "%s".', $filePath));
            }

            file_put_contents($filePath, $fixer->getContents());
        }
    }

    /**
     * @param string  $file
     * @param Ruleset $ruleset
     * @param Report  $report
     *
     * @return void
     */
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

    /**
     * @param Report $report
     * @param string $file
     *
     * @return void
     */
    private function setErrorHandler(Report $report, string $file): void
    {
        set_error_handler(static function (int $type, string $message) use ($report, $file): bool {
            if (E_USER_DEPRECATED === $type) {
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
