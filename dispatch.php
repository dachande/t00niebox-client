<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config/bootstrap.php');

if (empty($argv[1])) {
    die("Please specify a rfid uuid.\n");
}

$client = new \Dachande\T00nieBox\Client($argv[1]);
$client->run();
