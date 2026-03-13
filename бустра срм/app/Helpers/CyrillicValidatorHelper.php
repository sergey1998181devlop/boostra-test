<?php

if (!function_exists('validateCyrillic')) {
    /**
     * Проверяет, содержит ли строка только кириллические символы и пробелы
     *
     * @param string $value
     * @return bool
     */
    function validateCyrillic(string $value): bool
    {
        return (bool)preg_match('/^[А-ЯЁа-яё ]+$/u', $value);
    }
}

if (!function_exists('validateCyrillicPlus')) {
    /**
     * Проверяет, содержит ли строка только кириллические символы и дополнительные знаки
     *
     * @param string $value
     * @return bool
     */
    function validateCyrillicPlus(string $value): bool
    {
        return (bool)preg_match('/^[А-ЯЁа-яё0-9\\.\\,\\- №\/.–«»\\(\\)\\\\]+$/u', $value);
    }
}