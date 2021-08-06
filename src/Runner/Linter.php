<?php

declare(strict_types=1);

namespace TwigCsFixer\Runner;

use Exception;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Token\Tokenizer;

/**
 * Linter is the main class and will process twig files against a set of rules.
 */
final class Linter
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @param Environment $env
     * @param Tokenizer   $tokenizer
     *
     * @return void
     */
    public function __construct(Environment $env, Tokenizer $tokenizer)
    {
        $this->env = $env;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @param string[] $files
     * @param Ruleset  $ruleset
     * @param bool     $fix
     *
     * @return Report
     *
     * @throws Exception
     */
    public function run(array $files, Ruleset $ruleset, bool $fix = false): Report
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
            $this->setErrorHandler($report, $file);

            $this->processTemplate($file, $ruleset, $report);

            // Add this file to the report.
            $report->addFile($file);
        }
        restore_error_handler();

        // tearDown
        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->disable();
        }

        return $report;
    }

    /**
     * @param string[] $files
     * @param Ruleset  $ruleset
     *
     * @return void
     *
     * @throws Exception
     */
    private function fix(array $files, Ruleset $ruleset): void
    {
        $fixer = new Fixer($ruleset, $this->tokenizer);

        foreach ($ruleset->getSniffs() as $sniff) {
            $sniff->enableFixer($fixer);
        }

        foreach ($files as $file) {
            $success = $fixer->fixFile($file);

            if (!$success) {
                throw new Exception(sprintf('Cannot fix the file "%s".', $file));
            }

            file_put_contents($file, $fixer->getContents());
        }
    }

    /**
     * @param string  $file
     * @param Ruleset $ruleset
     * @param Report  $report
     *
     * @return bool
     */
    private function processTemplate(string $file, Ruleset $ruleset, Report $report): bool
    {
        $content = file_get_contents($file);
        if (false === $content) {
            $sniffViolation = new SniffViolation(
                Report::MESSAGE_TYPE_FATAL,
                sprintf('Unable to read file "%s"', $file),
                $file
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        $twigSource = new Source($content, $file);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (Error $e) {
            $sniffViolation = new SniffViolation(
                Report::MESSAGE_TYPE_FATAL,
                $e->getRawMessage(),
                $file,
                $e->getTemplateLine()
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        // Tokenizer.
        try {
            $stream = $this->tokenizer->tokenize($twigSource);
        } catch (Exception $exception) {
            $sniffViolation = new SniffViolation(
                Report::MESSAGE_TYPE_FATAL,
                sprintf('Unable to tokenize file: %s', $exception->getMessage()),
                $file
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        $sniffs = $ruleset->getSniffs();
        foreach ($sniffs as $sniff) {
            $sniff->processFile($stream);
        }

        return true;
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
                    Report::MESSAGE_TYPE_NOTICE,
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
