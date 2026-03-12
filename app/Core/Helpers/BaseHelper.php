<?php

use App\Core\Application\Application;
use App\Core\Helpers\Collection;
use Dotenv\Dotenv;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 3));
}

if (!function_exists('app')) {
    function app(): Application {
        return Application::singleton();
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null) {
        static $config = [];

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

        if ($key === null) {
            return $config;
        }

        // Разделение ключа на имя файла и ключ конфигурации
        $keys = explode('.', $key, 2);
        if (count($keys) === 1) {
            return $config[$keys[0]] ?? $default;
        }

        if (!isset($config[$keys[0]])) {
            return $default;
        }

        // Рекурсивное получение значения для многоуровневых конфигурационных массивов
        return array_reduce(explode('.', $keys[1]), function ($carry, $item) {
                return $carry[$item] ?? null;
            }, $config[$keys[0]]) ?? $default;
    }
}

if (!function_exists('env')) {
    /**
     * Get env data.
     */
    function env(string $key, string $default = null) {
        $dotenv = Dotenv::createImmutable(APP_ROOT . '/config');
        $dotenv->safeLoad();

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

if (!function_exists('collect')) {
    /**
     * Converts an array to a collection
     */
    function collect($array): Collection
    {
        return new Collection($array);
    }
}
