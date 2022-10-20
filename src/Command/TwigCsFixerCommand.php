<?php

declare(strict_types=1);

namespace TwigCsFixer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TwigCsFixer\Config\ConfigResolver;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\TextFormatter;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Token\Tokenizer;

/**
 * TwigCsFixer stands for "Twig Code Sniffer Fixer" and will check twig template of your project.
 */
final class TwigCsFixerCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('lint')
            ->setDescription('Lints a template and outputs encountered errors')
            ->setDefinition([
                new InputArgument(
                    'paths',
                    InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                    'Paths of files and folders to parse'
                ),
                new InputOption(
                    'level',
                    'l',
                    InputOption::VALUE_REQUIRED,
                    'Allowed values are notice, warning or error',
                    Report::MESSAGE_TYPE_NOTICE
                ),
                new InputOption(
                    'config',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'Path to a `.twig-cs-fixer.php` config file'
                ),
                new InputOption(
                    'fix',
                    'f',
                    InputOption::VALUE_NONE,
                    'Automatically fix all the fixable violations'
                ),
                new InputOption(
                    'no-cache',
                    '',
                    InputOption::VALUE_NONE,
                    'Disable cache while running the fixer'
                ),
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workingDir = getcwd();
        if (false === $workingDir) {
            return $this->fail($output, 'Cannot get the current working directory.');
        }

        try {
            // Resolve config
            $configResolver = new ConfigResolver($workingDir);
            $config = $configResolver->resolveConfig(
                $input->getArgument('paths'),
                $input->getOption('config'),
                $input->getOption('no-cache')
            );

            $cacheFile = $config->getCacheFile();
            if (null !== $cacheFile && is_file($cacheFile)) {
                $output->writeln(sprintf('Using cache file "%s".', $cacheFile));
            }

            // Execute the linter.
            $twig = new StubbedEnvironment($config->getTokenParsers());
            $linter = new Linter($twig, new Tokenizer($twig), $config->getCacheManager());

            // Build the report.
            $report = $linter->run(
                $config->getFinder(),
                $config->getRuleset(),
                $input->getOption('fix')
            );

            // Format the output.
            $reporter = new TextFormatter($input, $output);
            $reporter->display($report, $input->getOption('level'));
        } catch (Throwable $exception) {
            return $this->fail($output, $exception->getMessage());
        }

        // Return a meaningful error code.
        if ($report->getTotalErrors() > 0) {
            return 1;
        }

        return 0;
    }

    private function fail(OutputInterface $output, string $message): int
    {
        $output->writeln("<error>Error: {$message}</error>");

        return 1;
    }
}
