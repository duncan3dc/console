<?php

use duncan3dc\Console\Application;
use duncan3dc\Console\Command;

require __DIR__ . "/../vendor/autoload.php";

# Ensure that uopz doesn't muck about with the exit opcode
uopz_allow_exit(true);

$application = new Application();

$application->loadCommands(__DIR__ . "/commands/base");

$command = new Command("exception");
$command->setCode(function () {
    throw new \Exception("Whoops");
});

$application->addCommands([$command]);

$application->run();
