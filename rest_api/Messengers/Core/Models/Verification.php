<?php

namespace Messengers\Core\Models;

use Messengers\Config;
use Messengers\Core\DataBase\PDOConnect;

/**
 *
 */
final class Verification extends Model
{

    /**
     * Наименование таблицы в базе
     * @var string
     */
    protected static $tableName = Config::DB_VERIFY_USERS_TABLE_NAME;

    /**
     * Уникальные столбцы в таблице
     * @var string[]
     */
    protected static $uniqFields = [
        'sender_id',
        'messenger_type'
    ];

    /**
     * id отправителя в мессенджере
     * @var null
     */
    public $sender_id = null;

    /**
     * id отправителя (клиента) в базе данных
     * @var null
     */
    public $client_id = null;

    /**
     * Код верификации
     * @var null
     */
    public $verify_code = null;

    /**
     * Статус верификации
     * @var null
     */
    public $verify_status = null;

    /**
     * Шаг верификации
     * @var int
     */
    public $verify_step = 0;

    /**
     * Тип верификации мессенджера
     * @var null
     */
    public $messenger_type = null;

    /**
     * @return bool
     */
    public function init(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::$tableName . " (";
        $sql .= '`id` SERIAL, ';
        $sql .= '`uid` VARCHAR(40), ';
        $sql .= '`sender_id` VARCHAR(40), ';
        $sql .= '`messenger_type` VARCHAR(20), ';
        $sql .= '`client_id` INTEGER(11) DEFAULT NULL, ';
        $sql .= '`verify_code` INTEGER(6) DEFAULT NULL, ';
        $sql .= '`verify_status` INTEGER(1) DEFAULT NULL,  ';
        $sql .= '`verify_step`  INTEGER(2) DEFAULT NULL, ';
        $sql .= '`date_create` DATETIME DEFAULT CURRENT_TIMESTAMP, ';
        $sql .= '`date_update` DATETIME DEFAULT CURRENT_TIMESTAMP, ';
        $sql .= 'UNIQUE KEY `sender_id_ux` (`sender_id`),';
        $sql .= 'UNIQUE KEY `uid_ux` (`uid`) );';
        $sql .= " ALTER TABLE " . self::$tableName . " CONVERT TO CHARACTER SET utf8mb4;";
        return PDOConnect::instance()->prepare($sql)->execute();
    }

}