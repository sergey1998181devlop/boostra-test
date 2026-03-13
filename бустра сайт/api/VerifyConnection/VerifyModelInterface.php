<?php

namespace api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');

interface VerifyModelInterface
{
    public function create(VerifyBaseData $info);

    public function set(VerifyBaseData $info): bool;

    public function get(string $hash);
}