#!/usr/bin/env php
<?php

use duncan3dc\Console\Application;
use duncan3dc\Console\Command;

require __DIR__ . "/../vendor/autoload.php";

$application = new Application();

$command = new Command("exception");
$command->setCode(function () {
    throw new \Exception("Whoops");
});

$application->addCommands([$command]);

$application->run();
