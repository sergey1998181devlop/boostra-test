<?php

namespace Messengers\Core\Models;

use Messengers\Config;
use Messengers\Core\DataBase\PDOConnect;
use Simpla;

final class AuthCode extends Model
{
    protected static $tableName = Config::DB_AUT_CODES_TABLE_NAME;

    public static function addAuthCode(string $code, string $phone): void
    {
        $simpla = new Simpla();
        $simpla->authcodes->add_authcode(
            [
                'phone' => $phone,
                'code' => $code,
                'created' => date('Y-m-d H:i:s')
            ]
        );
    }

    public function init(): bool
    {
        $sql = "ALTER TABLE " . self::$tableName . " CONVERT TO CHARACTER SET utf8mb4;";
        return PDOConnect::instance()->prepare($sql)->execute();
    }
}