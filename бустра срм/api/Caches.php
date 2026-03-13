<?php

use App\Core\Cache\RedisCache;

require_once 'Simpla.php';

/**
 * Класс для работы с Redis-кешем
 *
 * Обеспечивает отказоустойчивое кеширование с автоматическим переключением
 * на прямые запросы при недоступности Redis
 */
class Caches extends Simpla
{
    private $cache_instance = null;
    private $redis_enabled = false;

    /**
     * Инициализация Redis-подключения
     *
     * Пытается получить Redis-реализацию из контейнера и проверяет её доступность.
     * При ошибке устанавливает redis_enabled в false для отказоустойчивой работы.
     */
    public function __construct()
    {
        parent::__construct();
        try {
            // Пытаемся получить именно Redis-реализацию из контейнера
            $this->cache_instance = \App\Core\Application\Application::singleton()->make(\App\Core\Cache\CacheInterface::class);

            // Проверяем, что это именно Redis и он «жив»
            if ($this->cache_instance instanceof RedisCache) {
                $this->redis_enabled = true;
            }
        } catch (\Exception $e) {
            $this->cache_instance = null;
            $this->redis_enabled = false;
        }
    }

    /**
     * Прямой доступ к Redis-инстансу
     *
     * Используется для специфических операций, не покрытых базовыми методами
     *
     * @return RedisCache|null Инстанс Redis или null если Redis недоступен
     */
    public function redis()
    {
        return $this->redis_enabled ? $this->cache_instance : null;
    }

    /**
     * Получение значения из кеша по ключу
     *
     * @param string $key Ключ кеша
     * @return mixed|null Закешированное значение (любой тип: array, object, string, int, bool, null) или null если ключ не найден/Redis недоступен
     */
    public function get(string $key)
    {
        if (!$this->redis_enabled) return null;

        try {
            return $this->cache_instance->get($key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Сохранение значения в кеш
     *
     * @param string $key Ключ кеша
     * @param mixed $data Данные для сохранения (поддерживаются любые типы: array, object, string, int, bool, null, float)
     * @param int $ttl Время жизни кеша в секундах (по умолчанию 600 секунд = 10 минут)
     * @return bool true если сохранение успешно, false если Redis недоступен или произошла ошибка
     */
    public function set(string $key, $data, int $ttl = 600): bool
    {
        if (!$this->redis_enabled) {
            return false;
        }

        try {
            $this->cache_instance->set($key, $data, $ttl);
            return true;
        } catch (\Exception $e) {
            $this->logging(__METHOD__, 'redis_cache_set', ['key' => $key, 'ttl' => $ttl], ['error' => $e->getMessage()], 'redis_cache.txt');
            return false;
        }
    }

    /**
     * Удаление значения из кеша по ключу
     *
     * @param string $key Ключ кеша для удаления
     * @return bool true если удаление успешно или Redis недоступен, false если произошла ошибка
     */
    public function delete(string $key): bool
    {
        if (!$this->redis_enabled) {
            return true;
        }

        try {
            $this->cache_instance->delete($key);
            return true;
        } catch (\Exception $e) {
            $this->logging(__METHOD__, 'redis_cache_delete', ['key' => $key], ['error' => $e->getMessage()], 'redis_cache.txt');
            return false;
        }
    }

    /**
     * Кеширование с поддержкой null/false значений
     *
     * Использует wrapper-объект для различения "не в кеше" и "закеширован null/false"
     * Формат: ['_h' => 1, 'v' => $actualValue]
     * '_h' (hit marker) - маркер валидного кеша (короткий ключ для экономии памяти)
     * 'v' (value) - фактическое значение
     *
     * @param string $key Ключ кеша
     * @param int $ttl Время жизни кеша в секундах
     * @param callable $callback Функция для получения данных, если кеш пуст (должна возвращать mixed)
     * @return mixed Закешированное или свежее значение (любой тип: array, object, string, int, bool, null, float)
     */
    public function wrap($key, $ttl, $callback)
    {
        // Early return если Redis выключен
        if (!$this->redis_enabled) {
            return $callback();
        }

        // 1. Пробуем взять из Redis
        $cached = $this->get($key);

        // Проверяем наличие маркера валидного кеша
        if (isset($cached['_h'])) {
            return $cached['v'];
        }

        // 2. Кеша нет — вызываем callback
        $data = $callback();

        // 3. Сохраняем обёрнутое значение (даже если это null/false)
        $this->set($key, ['_h' => 1, 'v' => $data], $ttl);

        return $data;
    }
}