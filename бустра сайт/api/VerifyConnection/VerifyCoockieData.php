<?php

namespace Api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyConnection.php');

class VerifyCoockieData extends VerifyBaseData
{
    public $hash;
    public $coockie;
    public $count = 0;

    public function __construct(string $coockie)
    {
        $this->coockie = $coockie;
        $this->hash = VerifyConnection::hash($this->coockie);
    }

    public function countIncrement(): int
    {
        return ++$this->count;
    }
}