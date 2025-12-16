<?php

namespace api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyConnectionAdapter.php');

class VerifyConnection
{
    protected static $maxConnection = 5;
    protected static $expirationTime = '-1minutes';
    protected $verifyConnectionData;
    protected $verifyConnectionAdapter;

    public function __construct(
        VerifyBaseData $verifyConnectionData,
        VerifyConnectionAdapter $verifyConnectionAdapter
    ) {
        $this->verifyConnectionData = $verifyConnectionData;
        $this->verifyConnectionAdapter = $verifyConnectionAdapter;
    }

    public function create()
    {
        return $this->verifyConnectionAdapter->create($this->verifyConnectionData);
    }

    public function set(): bool
    {
        return $this->verifyConnectionAdapter->set($this->verifyConnectionData);
    }

    public function get(string $dataSearch)
    {
        return $this->verifyConnectionAdapter->get($this->hash($dataSearch));
    }

    public static function maxConnection(): int
    {
        return self::$maxConnection;
    }

    public static function canSet($data): bool
    {
        if (empty($data->count)) return true;
        $timeLeft = date('Y-m-d H:i:s', strtotime('now ' . self::$expirationTime));
        $checkTime = ($timeLeft > $data->updated_at);
        return ($checkTime || (self::maxConnection() >= $data->count));
    }

    public function setVerifyConnectionData(VerifyBaseData $verifyConnectionData)
    {
        $this->verifyConnectionData = $verifyConnectionData;
    }

    public static function hash(string $dataToHash): string
    {
        return hash('sha256', $dataToHash);
    }
}