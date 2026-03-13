<?php

namespace App\Core\Application\Session;

class Flash
{
    private const FLASH_KEY = '_flash';

    public static function set(string $key, $value): void
    {
        if (!isset($_SESSION[self::FLASH_KEY])) {
            $_SESSION[self::FLASH_KEY] = [];
        }
        $_SESSION[self::FLASH_KEY][$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        if (!isset($_SESSION[self::FLASH_KEY][$key])) {
            return $default;
        }

        $value = $_SESSION[self::FLASH_KEY][$key];
        unset($_SESSION[self::FLASH_KEY][$key]);

        return $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    public static function all(): array
    {
        $flash = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);
        return $flash;
    }

    public static function clear(): void
    {
        unset($_SESSION[self::FLASH_KEY]);
    }
} 