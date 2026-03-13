<?php

namespace App\Modules\TicketAssignment\Contracts;

/**
 * Интерфейс для работы с настройками системы
 */
interface SettingsInterface
{
    /**
     * Получить значение настройки
     *
     * @param string $key Ключ настройки
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Установить значение настройки
     *
     * @param string $key Ключ настройки
     * @param mixed $value Значение
     * @return bool
     */
    public function set(string $key, $value): bool;
}
