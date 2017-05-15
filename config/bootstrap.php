<?php

// Load paths
require __DIR__ . '/paths.php';

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Log\Log;
use Cake\Console\ConsoleOutput;
use Dachande\T00nieBox\Error\ErrorHandler;

// Configuration
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

// Implement some basic methods from CakePHP
require APP . '/basics.php';

// Initialize logging
Log::setConfig(Configure::consume('Log'));

// Override console colors
if (Configure::check('Console.styles')) {
    foreach (Configure::consume('Console.styles') as $style => $definition) {
        ConsoleOutput::styles($style, $definition);
    }
}

// Exception handling
(new ErrorHandler())->register();
