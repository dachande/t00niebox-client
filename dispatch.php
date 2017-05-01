<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/paths.php');

$client = new \Dachande\T00nieBox\Client();

if (empty($argv[1])) {
    die("Please specify a rfid uuid.\n");
}

$client->setUuid($argv[1]);
$client->run();
