<?php

use duncan3dc\Console\Application;
use duncan3dc\Console\Command;

require __DIR__ . "/../vendor/autoload.php";

$application = new Application();

$application->loadCommands(__DIR__ . "/commands/base");

$command = new Command("exception");
$command->setCode(function () {
    throw new \Exception("Whoops");
});

$application->addCommands([$command]);

$application->run();
