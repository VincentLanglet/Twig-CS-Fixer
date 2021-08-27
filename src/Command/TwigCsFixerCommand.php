<?php

declare(strict_types=1);

namespace TwigCsFixer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Throwable;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Config\ConfigResolver;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\TextFormatter;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Token\Tokenizer;

use function getcwd;

/**
 * TwigCsFixer stands for "Twig Code Sniffer Fixer" and will check twig template of your project.
 */
final class TwigCsFixerCommand extends Command
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
                    'exclude',
                    'e',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'Excludes, based on regex, paths of files and folders from parsing',
                    ['vendor/']
                ),
            ]);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workingDir = getcwd();
        if (false === $workingDir) {
            return $this->fail($output, 'Cannot get the current working directory.');
        }

        try {
            // Execute the linter.
            $twig = new StubbedEnvironment();
            $linter = new Linter($twig, new Tokenizer($twig));

            // Resolve config
            $configResolver = new ConfigResolver($workingDir);
            $config = $configResolver->getConfig($input->getOption('config'));
            $paths = $input->getArgument('paths');

            // Get the file finder and add the path data.
            $finder = $config->getFinder();
            // @todo This overwrites what the user provided if they supplied
            //   their own finder. If we don't add it here though, I'm not sure
            //   where else we could add this data. Doing this in Config.php
            //   seems like we are bringing too many concerns into that class.
            //   Perhaps we would do this in Finder.php or what is your idea?
            $finder->path($paths);
            // @todo I realize that you are proposing that we don't include this
            //   flag and instead rely upon a twig-cs-fixer.php file. What is
            //   the benefit of that approach? The flag seems much easier for a
            //   new user to find and figure out to me.
            // Exclude files if the flag was included.
            try {
                if ($exclude = $input->getOption('exclude')) {
                    $finder->exclude($exclude);
                }
            }
            catch (InvalidArgumentException $exception) { }

            // Build the report.
            $report = $linter->run($finder, $config->getRuleset(), $input->getOption('fix'));

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

    /**
     * @param OutputInterface $output
     * @param string          $message
     *
     * @return int
     */
    private function fail(OutputInterface $output, string $message): int
    {
        $output->writeln("<error>Error: $message</error>");

        return 1;
    }
}
