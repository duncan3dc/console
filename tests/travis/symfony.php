#!/usr/bin/env php
<?php

if (!getenv("SYMFONY_VERSION")) {
    return;
}

$path = __DIR__ . "/../../composer.json";

$json = file_get_contents($path);
$composer = json_decode($json);

foreach ($composer->require as $package => &$version) {
    if (substr($package, 0, 11) === "symfony/") {
        $version = "^" . getenv("SYMFONY_VERSION");
    }
}
unset($version);

$json = json_encode($composer);
file_put_contents($path, $json);
