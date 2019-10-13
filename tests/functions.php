<?php

function runApplication(string $command): \stdClass
{
    $output = [];
    $exit = -1;

    $exec = PHP_BINARY . " " . __DIR__ . "/app.php {$command}";
    exec($exec, $output, $exit);

    return (object) [
        "exit" => $exit,
        "output" => $output,
    ];
}
