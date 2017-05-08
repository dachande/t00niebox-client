<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config/bootstrap.php');

$client = new \Dachande\T00nieBox\Client();

if (!empty($argv[1])) {
    $client->setUuid($argv[1]);
}

$client->run();
