<?php

define('ROOT_DIR', dirname(__DIR__, 2));
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');


require_once ROOT_DIR . '/api/Simpla.php';
require_once ROOT_DIR . '/vendor/autoload.php';

