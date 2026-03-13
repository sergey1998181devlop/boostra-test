<?php

namespace App\Core\Cache;

/**
 * Интерфейс для работы с кэшем
 */
interface CacheInterface
{
    /**
     * Получить данные из кэша
     *
     * @param string $key Ключ кэша
     * @return mixed Данные или null если ключ не найден
     */
    public function get(string $key);

    /**
     * Сохранить данные в кэш
     *
     * @param string $key Ключ кэша
     * @param mixed $data Данные для сохранения
     * @param int $ttl Время жизни в секундах
     * @return void
     */
    public function set(string $key, $data, int $ttl = 600): void;

    /**
     * Удалить данные из кэша
     *
     * @param string $key Ключ кэша
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Проверить существование ключа в кэше
     *
     * @param string $key Ключ кэша
     * @return bool True если ключ существует
     */
    public function exists(string $key): bool;
}