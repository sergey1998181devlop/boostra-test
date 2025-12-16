<?php

namespace Messengers\Models;

use JsonException;
use Messengers\Config;
use Messengers\DataBase\PDOConnect;
use Messengers\Dialogs\VerificationDialog;
use Messengers\Main;
use Messengers\MessageHtmlBody;

final class Message extends Model
{
    /**
     * Наименование таблицы в базе данных
     * @var string
     */
    protected static $tableName = Config::DB_MESSAGES_TABLE_NAME;

    /**
     * Уникальные столбцы в таблице
     * @var string[]
     */
    protected static $uniqFields = [
        'message_id'
    ];

    /**
     * Тип мессенджера отправителя
     * @var string|null
     */
    public $messenger_type = null;

    /**
     * id получателя (менеджера) в системе
     * @var int
     */
    public $manager_id = 0; //

    /**
     * Статус сообщения
     * @var int
     */
    public $status = 0; //

    /**
     * Отправитель сообщения
     * 0 - менеджер
     * 1 - клиент
     * @var int
     */
    public $is_client = 0;

    /**
     * id клиента отправителя в системе
     * @var int
     */
    public $client_id = 0;

    /**
     * id сообщения в мессенджере
     * @var null
     */
    public $message_id = null; //

    /**
     * Тело сообщения
     * @var null
     */
    public $body = null;

    /**
     * id отправителя в мессенджере
     * @var null
     */
    public $sender_id = null;

    /**
     * id чата в мессенджере
     * @var null
     */
    public $chat_id = null; //

    /**
     * Инициализация таблицы в базе данных
     * @return bool
     */
    public function init(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::$tableName . " (";
        $sql .= '`id` SERIAL, ';
        $sql .= '`uid` VARCHAR(40), ';
        $sql .= '`chat_id` VARCHAR(50), ';
        $sql .= '`is_client` INTEGER(1) DEFAULT 0, ';
        $sql .= '`sender_id` VARCHAR(40), ';
        $sql .= '`body` BLOB, ';
        $sql .= '`message_id` VARCHAR(40), ';
        $sql .= '`client_id` INTEGER(11) DEFAULT NULL, ';
        $sql .= '`status` INTEGER(1) DEFAULT NULL, ';
        $sql .= '`manager_id` INTEGER(11) DEFAULT NULL, ';
        $sql .= '`messenger_type` VARCHAR(20), ';
        $sql .= '`date_create` DATETIME DEFAULT CURRENT_TIMESTAMP, ';
        $sql .= '`date_update` DATETIME DEFAULT CURRENT_TIMESTAMP, ';
        $sql .= 'UNIQUE KEY `uid_ux` (`uid`) );';
        $sql .= 'UNIQUE KEY `message_id_ux` (`message_id`) );';
        $sql .= " ALTER TABLE " . self::$tableName . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
        return PDOConnect::instance()->prepare($sql)->execute();
    }

    public static function getManagerId(self $message): int
    {
        return 0;
    }


    /**
     * @return $this|null
     * @throws JsonException
     */
    public function send(): ?self
    {
        $provider = Main::getProvider($this->messenger_type);
        if ($provider) {
            $message = $provider->sendText($this);
            if ($message) {
                $sendMessage = new self();
                foreach ($message as $field => $value) {
                    $sendMessage->$field = $value;
                }
                $verification = new VerificationDialog($message);
                $sendMessage->client_id = $verification->getVerification()->client_id;
                $sendMessage->body = MessageHtmlBody::text($message->body);
                return $sendMessage->save();
            }
        }
        return null;
    }


}