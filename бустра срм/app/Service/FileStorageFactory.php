<?php

namespace App\Service;

use InvalidArgumentException;

class FileStorageFactory
{
    private array $profiles;

    public function __construct()
    {
        $this->profiles = (array)config('storage.profiles', []);
    }

    public function make(string $profile): FileStorageService
    {
        if (!isset($this->profiles[$profile])) {
            throw new InvalidArgumentException("Unknown storage profile: {$profile}");
        }

        $cfg = $this->profiles[$profile];
        $this->validateConfig($profile, $cfg);

        return new FileStorageService(
            $cfg['url'],
            $cfg['region'],
            $cfg['access_key'],
            $cfg['secret_key'],
            $cfg['bucket']
        );
    }

    private function validateConfig(string $profile, array $config): void
    {
        $required = ['url', 'region', 'access_key', 'secret_key', 'bucket'];

        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new InvalidArgumentException("Storage profile '{$profile}' missing required parameter: {$key}");
            }
        }
    }
}
