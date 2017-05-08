<?php

require __DIR__ . '/paths.php';

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

require APP . '/basics.php';
