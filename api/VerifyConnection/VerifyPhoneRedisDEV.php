<?php

namespace Api\VerifyConnection;

require_once ROOT . DIRECTORY_SEPARATOR.'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class VerifyPhoneRedis implements VerifyModelInterface
{
    const HASH_NAME = 'verify_phones';

    public function __construct()
    {

    }

    public function set($info): bool
    {
        $client = new \Predis\Client();
        $client->set('foo', 'bar');
        return (Redis::command('HSET', [self::HASH_NAME, $info->phone_hash, json_encode((array)$info)])) ? true: false;
    }

    public function get(string $hash)
    {
        return Redis::command('HGET', [self::HASH_NAME, $hash]);
    }
}