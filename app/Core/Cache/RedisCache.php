<?php

namespace App\Core\Cache;

use App\Core\Exception\Exceptions\CacheException;

/**
 * Реализация кэша на основе Redis
 */
class RedisCache implements CacheInterface
{
    /**
     * @var object Redis client instance (Predis\Client or Redis extension)
     */
    private object $redis;

    /**
     * @param object|null $redis Predis\Client or Redis extension instance
     * @throws CacheException
     */
    public function __construct(?object $redis = null)
    {
        if ($redis === null) {
            $config = [
                'host' => config('cache.redis.host', 'redis'),
                'port' => config('cache.redis.port', 6379),
                'database' => config('cache.redis.database', 0),
            ];

            if ($timeout = config('cache.redis.timeout')) {
                $config['timeout'] = $timeout;
            }

            try {
                $this->redis = $this->createClient($config);
            } catch (\Exception $e) {
                throw new CacheException('Failed to connect to Redis: ' . $e->getMessage(), 0, $e);
            }
        } else {
            $this->redis = $redis;
        }
    }

    /**
     * @throws CacheException
     */
    public function get(string $key)
    {
        try {
            $cached = $this->redis->get($key);
        } catch (\Exception $e) {
            throw new CacheException("Failed to get key '{$key}' from Redis: " . $e->getMessage(), 0, $e);
        }

        if ($cached === null || $cached === false) {
            return null;
        }

        return unserialize($cached);
    }

    /**
     * @throws CacheException
     */
    public function set(string $key, $data, int $ttl = 600): void
    {
        try {
            $serialized = serialize($data);
            $this->redis->setex($key, $ttl, $serialized);
        } catch (\Exception $e) {
            throw new CacheException("Failed to set key '{$key}' in Redis: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws CacheException
     */
    public function delete(string $key): void
    {
        try {
            $this->redis->del($key);
        } catch (\Exception $e) {
            throw new CacheException("Failed to delete key '{$key}' from Redis: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws CacheException
     */
    public function exists(string $key): bool
    {
        try {
            return (bool) $this->redis->exists($key);
        } catch (\Exception $e) {
            throw new CacheException("Failed to check existence of key '{$key}' in Redis: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws CacheException
     */
    public function hSet(string $key, string $field, $value): void
    {
        try {
            $this->redis->hset($key, $field, serialize($value));
        } catch (\Exception $e) {
            throw new CacheException("Failed to hSet field '{$field}' in key '{$key}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws CacheException
     */
    public function hGet(string $key, string $field)
    {
        try {
            $result = $this->redis->hget($key, $field);
        } catch (\Exception $e) {
            throw new CacheException("Failed to hGet field '{$field}' from key '{$key}': " . $e->getMessage(), 0, $e);
        }

        if ($result === null || $result === false) {
            return null;
        }

        return unserialize($result);
    }

    /**
     * @throws CacheException
     */
    public function hDel(string $key, string $field): void
    {
        try {
            $this->redis->hdel($key, $field);
        } catch (\Exception $e) {
            throw new CacheException("Failed to hDel field '{$field}' from key '{$key}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws CacheException
     */
    public function hGetAll(string $key): array
    {
        try {
            $raw = $this->redis->hgetall($key);
        } catch (\Exception $e) {
            throw new CacheException("Failed to hGetAll from key '{$key}': " . $e->getMessage(), 0, $e);
        }

        if (empty($raw)) {
            return [];
        }

        $result = [];
        foreach ($raw as $field => $value) {
            $result[$field] = unserialize($value);
        }

        return $result;
    }

    /**
     * @throws CacheException
     */
    public function hExists(string $key, string $field): bool
    {
        try {
            return (bool) $this->redis->hexists($key, $field);
        } catch (\Exception $e) {
            throw new CacheException("Failed to hExists field '{$field}' in key '{$key}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws CacheException
     */
    public function expire(string $key, int $ttl): void
    {
        try {
            $this->redis->expire($key, $ttl);
        } catch (\Exception $e) {
            throw new CacheException("Failed to set expire on key '{$key}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $config
     * @throws CacheException
     */
    private function createClient(array $config): object
    {
        if (class_exists(\Predis\Client::class)) {
            return new \Predis\Client($config);
        }

        if (class_exists(\Redis::class)) {
            $client = new \Redis();
            $timeout = isset($config['timeout']) ? (float) $config['timeout'] : 0.0;
            $client->connect((string) $config['host'], (int) $config['port'], $timeout);
            if (!empty($config['database'])) {
                $client->select((int) $config['database']);
            }

            return $client;
        }

        throw new CacheException('Redis client library not installed. Install predis/predis or enable ext-redis.');
    }
}
