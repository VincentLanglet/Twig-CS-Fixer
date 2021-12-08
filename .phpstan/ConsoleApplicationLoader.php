<?php

use Symfony\Component\Console\Application;
use TwigCsFixer\Command\TwigCsFixerCommand;

require __DIR__.'/../vendor/autoload.php';

$command = new TwigCsFixerCommand();

$application = new Application();
$application->add($command);

return $application;
