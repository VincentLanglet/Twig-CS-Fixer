<?php

declare(strict_types=1);

namespace TwigCsFixer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TwigCsFixer\Config\Config;
use TwigCsFixer\Config\ConfigResolver;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Exception\CannotResolveConfigException;
use TwigCsFixer\Report\Report;
use TwigCsFixer\Report\TextFormatter;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Token\Tokenizer;
use Webmozart\Assert\Assert;

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
        try {
            $config = $this->resolveConfig($input, $output);
            $report = $this->runLinter($config, $input, $output);
        } catch (Throwable $exception) {
            $output->writeln(sprintf('<error>Error: %s</error>', $exception->getMessage()));

            return self::INVALID;
        }

        return 0 === $report->getTotalErrors() ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @throws CannotResolveConfigException
     */
    private function resolveConfig(InputInterface $input, OutputInterface $output): Config
    {
        $workingDir = @getcwd();
        Assert::notFalse($workingDir, 'Cannot get the current working directory.');

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

        return $config;
    }

    private function runLinter(Config $config, InputInterface $input, OutputInterface $output): Report
    {
        $twig = new StubbedEnvironment(
            $config->getTwigExtensions(),
            $config->getTokenParsers()
        );
        $tokenizer = new Tokenizer($twig);
        $linter = new Linter($twig, $tokenizer, $config->getCacheManager());

        $report = $linter->run(
            $config->getFinder(),
            $config->getRuleset(),
            $input->getOption('fix') ? new Fixer($tokenizer) : null
        );

        $reporter = new TextFormatter($input, $output);
        $reporter->display($report, $input->getOption('level'));

        return $report;
    }
}
