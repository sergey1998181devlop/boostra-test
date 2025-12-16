<?php

namespace Messengers\DataBase;

use Messengers\Config;
use PDO;

class PDOConnect
{
    /**
     * @var PDO
     */
    private static $connect;

    private function __construct()
    {
        self::$connect = new PDO(
            Config::DB_DSN,
            Config::DB_USER,
            Config::DB_PASSWORD,
            Config::DB_OPTIONS
        );
    }

    public static function instance(): PDO
    {
        if (!self::$connect) {
            new self();
        }
        return self::$connect;
    }
}