<?php

require_once __DIR__ . '/bootstrap.php';

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('log_errors', 'On');
ini_set('error_log', 'logs/error.log');


/**
 * Define root directory.
 */

use App\Core\Application\Application;

/**
 * Create Application object instance.
 */

$app = Application::singleton();

/**
 * Run The Application.
 */
$app->run();
