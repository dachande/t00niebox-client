<?php

require __DIR__ . '/paths.php';

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Log\Log;
use Dachande\T00nieBox\Error\ErrorHandler;

// Configuration
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

// Logging
Log::setConfig(Configure::consume('Log'));

// Exception handling
(new ErrorHandler())->register();

// Implement some basic methods from CakePHP
require APP . '/basics.php';
