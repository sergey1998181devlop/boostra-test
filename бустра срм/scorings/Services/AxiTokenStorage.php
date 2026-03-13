<?php

require_once(__DIR__ . '/../../api/Simpla.php');

class AxiTokenStorage extends Simpla
{
    private const STORAGE_KEY_PREFIX = 'AxiTokenStorage_';

    private const TTL = 500;
    
    private function buildStorageKey(string $key): string
    {
        return self::STORAGE_KEY_PREFIX . $key;
    }
    
    public function set(string $key, string $token): bool
    {
        $key = self::buildStorageKey($key);
        
        return $this->caches->set($key, $token, self::TTL);
    }

    public function get(string $key): ?string
    {
        $key = self::buildStorageKey($key);

        return $this->caches->get($key);
    }
}