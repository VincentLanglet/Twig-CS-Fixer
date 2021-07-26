<?php

declare(strict_types=1);

namespace TwigCsFixer\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\TextFormatter;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Token\Tokenizer;

/**
 * TwigCsFixer stands for "Twig Code Sniffer Fixer" and will check twig template of your project.
 */
class TwigCsFixerCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('lint')
            ->setDescription('Lints a template and outputs encountered errors')
            ->setDefinition([
                new InputOption(
                    'level',
                    'l',
                    InputOption::VALUE_OPTIONAL,
                    'Allowed values are notice, warning or error',
                    'notice'
                ),
                new InputOption(
                    'fix',
                    'f',
                    InputOption::VALUE_NONE,
                    'Automatically fix all the fixable violations'
                ),
            ])
            ->addArgument(
                'paths',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Paths of files and folders to parse',
                []
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $paths = $input->getArgument('paths');
        \assert(is_array($paths));
        $level = $input->getOption('level');
        \assert(is_string($level));
        $fix = $input->getOption('fix');
        \assert(is_bool($fix));

        $config = new Config($paths);

        // Get the rules to apply.
        $ruleset = new Ruleset();
        $ruleset->addStandard();

        // Execute the linter.
        $twig = new StubbedEnvironment();
        $linter = new Linter($twig, new Tokenizer($twig));
        $report = $linter->run($config->findFiles(), $ruleset, $fix);

        // Format the output.
        $reporter = new TextFormatter($input, $output);
        $reporter->display($report, $level);

        // Return a meaningful error code.
        if ($report->getTotalErrors() > 0) {
            return 1;
        }

        return 0;
    }
}
