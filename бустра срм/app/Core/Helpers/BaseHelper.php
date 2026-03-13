<?php

use App\Core\Application\Application;
use Dotenv\Dotenv;

const APP_HELPERS_DIR = __DIR__ . '/../../..';
const APP_CORE_DIR = __DIR__ . '/../..';
const APP_DIR = __DIR__ . '/..';
const ROOT_DIR = __DIR__;

// Автоматически определяем APP_ROOT если он не определен
if (!defined('APP_ROOT')) {
    // Определяем корневую папку проекта
    // Ищем папку с composer.json или config.php
    $possibleRoots = [
        APP_HELPERS_DIR, // если файл в app/Core/Helpers/
        APP_CORE_DIR,    // если файл в app/Core/
        APP_DIR,         // если файл в app/
        ROOT_DIR,        // если файл в корне
    ];

    foreach ($possibleRoots as $root) {
        $realRoot = realpath($root);
        if (
                $realRoot && (
                file_exists($realRoot . '/composer.json') ||
                file_exists($realRoot . '/config/config.php') ||
                file_exists($realRoot . '/config/.env'
            )
        )) {
            define('APP_ROOT', $realRoot);
            break;
        }
    }

    // Если ничего не найдено, используем текущую папку
    if (!defined('APP_ROOT')) {
        define('APP_ROOT', APP_CORE_DIR);
    }
}

if (!function_exists('app')) {
    function app(): Application {
        return Application::singleton();
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null) {
        static $config = [];
        static $envLoaded = false;

        // Загружаем .env файл только один раз
        if (!$envLoaded) {
            $dotenv = Dotenv::createImmutable(APP_ROOT . '/config');
            $dotenv->safeLoad();
            $envLoaded = true;
        }

        // Загружаем конфигурационные файлы только один раз
        if (empty($config)) {
            $configDirectory = APP_ROOT . '/app/config/';
            $configFiles = scandir($configDirectory);

            foreach ($configFiles as $file) {
                if (strpos($file, '.php')) {
                    $fileName = basename($file, '.php');
                    $config[$fileName] = require $configDirectory . $file;
                }
            }
        }

        // Если ключ не передан, возвращаем всю конфигурацию
        if ($key === null) {
            return $config;
        }

        // Разбираем ключ типа "services.recurrent_center.api_token"
        $parts = explode('.', $key);
        
        // Начинаем с корневого массива конфигурации
        $value = $config;
        
        // Проходим по каждой части ключа
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        
        return $value ?? $default;
    }
}

if (!function_exists('env')) {
    /**
     * Get env data.
     */
    function env(string $key, ?string $default = null) {
        static $loaded = false;

        if (!$loaded) {
            $dotenv = Dotenv::createImmutable(APP_ROOT . '/config');
            $dotenv->safeLoad();
            $loaded = true;
        }

        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    /**
     * View the data in details then exit the code.
     */
    function dd($value): void {
        if (is_string($value)) {
            echo $value;
        } else {
            echo '<pre>';
            print_r($value);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('isProduction')) {
    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    function isProduction(): bool
    {
        return config('app.env') === 'production';
    }
}