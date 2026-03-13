<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('log_errors', 'On');
ini_set('error_log', 'logs/error.log');

/**
 * Define root directory.
 */

use App\Core\Application\Application;

const APP_ROOT = __DIR__;

/**
 * Register The Auto Loader.
 */
require_once APP_ROOT . '/vendor/autoload.php';

/**
 * Create Application object instance.
 */

$app = Application::singleton();

/**
 * Require registered Route.
 */
require_once APP_ROOT . '/routes/api.php';

/**
 * Run The Application.
 */
$app->run();
