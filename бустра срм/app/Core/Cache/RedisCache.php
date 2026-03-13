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
    
    public function set(string $key, $data, int $ttl = 600): void
    {
        try {
            $serialized = serialize($data);
            $this->redis->setex($key, $ttl, $serialized);
        } catch (\Exception $e) {
            throw new CacheException("Failed to set key '{$key}' in Redis: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function delete(string $key): void
    {
        try {
            $this->redis->del($key);
        } catch (\Exception $e) {
            throw new CacheException("Failed to delete key '{$key}' from Redis: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function exists(string $key): bool
    {
        try {
            return (bool) $this->redis->exists($key);
        } catch (\Exception $e) {
            throw new CacheException("Failed to check existence of key '{$key}' in Redis: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $config
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
