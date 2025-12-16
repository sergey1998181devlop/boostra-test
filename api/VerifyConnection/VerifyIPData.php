<?php

namespace Api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyConnection.php');

class VerifyIPData extends VerifyBaseData
{
    public $hash;
    public $ip;
    public $count = 0;

    public function __construct(string $ip)
    {
        $this->ip = $ip;
        $this->hash = VerifyConnection::hash($this->ip);
    }

    public function countIncrement(): int
    {
        return ++$this->count;
    }
}