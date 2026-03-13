<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', -1);

if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $protocol = 'https://';
} else {
    $protocol = 'http://';
}

define('protocol', $protocol);
define('baseUrl', $_SERVER['SERVER_NAME']);
define('ROOT', __DIR__);
define('uploadDir', ROOT . DIRECTORY_SEPARATOR . 'chats' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR);
define('mangoDir', uploadDir . 'mango' . DIRECTORY_SEPARATOR);
define('mangoLogsDir', mangoDir . 'logs' . DIRECTORY_SEPARATOR);
session_start();

if (!is_dir(uploadDir)) {
    mkdir(uploadDir);
}

spl_autoload_register(function ($className) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, __DIR__ . DIRECTORY_SEPARATOR . $className . '.php');
    if (!is_file($file)) {
        $file = ROOT . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . $className . '.php';
    }
    if (is_file($file))
        include_once $file;
    else {
        header('400 Bad Request');
        echo json_encode(['error' => 'Класс ' . $className . ' не установлен']);
        exit();
    }
});

$chat = $_REQUEST['chat'] ?? '';
$class = $_REQUEST['class'] ?? '';
$method = $_REQUEST['method'] ?? '';

$new = 'chats\\' . strtolower($chat) . '\\' . ucfirst(strtolower($chat)) . ucfirst($class);
if ($chat) {
    if (class_exists($new)) {
        $obj = new $new();
        if (method_exists($obj, $method)) {
            $data = $_REQUEST;
            unset($data['chat'], $data['class'], $data['method']);
            $content = $obj->$method($data);
            echo json_encode($content);
        } else {
            header('400 Bad Request');
            echo json_encode(['error' => 'Метод ' . $method . ' в классе ' . $new . ' не установлен']);
            exit();
        }
    } else {
        header('400 Bad Request');
        echo json_encode(['error' => 'Класс ' . $new . ' не установлен']);
        exit();
    }
} else {
    header('400 Bad Request');
    echo json_encode(['error' => 'Класс ' . $new . ' не установлен']);
    exit();
}
