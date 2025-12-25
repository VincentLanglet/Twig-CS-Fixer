<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;
use TwigCsFixer\Console\Command\TwigCsFixerCommand;

require __DIR__.'/../vendor/autoload.php';

$command = new TwigCsFixerCommand();

$application = new Application();
$application->addCommands([$command]);

return $application;
