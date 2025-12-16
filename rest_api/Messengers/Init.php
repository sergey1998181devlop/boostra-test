<?php

namespace Messengers;

use Messengers\Core\DataBase\PDOConnect;
use Messengers\Core\Models\AuthCode;
use Messengers\Core\Models\Message;
use Messengers\Core\Models\Verification;
use Messengers\Telegram\Telegram;
use Messengers\Viber\Viber;
use Messengers\Vk\Vk;
use Messengers\WhatsApp\WhatsApp;

final class Init implements ProviderInterface
{

    public const PROVIDERS = [
        'viber' => Viber::class,
        'vk' => Vk::class,
        'whatsapp' => WhatsApp::class,
        'telegram' => Telegram::class
    ];

    /**
     * @var array
     */
    public static $errors = [];

    /**
     * @var bool|null
     */
    public $tables = null;

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $sql = "DROP TABLE " . Config::DB_MESSAGES_TABLE_NAME . '; ';
        $sql .= "DROP TABLE " . Config::DB_VERIFY_USERS_TABLE_NAME;
        return PDOConnect::instance()->prepare($sql)->execute();
    }

    public function init(): self
    {
        foreach (self::PROVIDERS as $messenger => $provider) {
            $providerObj = new $provider;
            if (method_exists($providerObj, 'init')) {
                $this->$messenger = $providerObj->init();
            }
        }
        $this->tables = [
            'chat' => (new Message())->init(),
            'verification' => (new Verification())->init(),
            'auth_codes' => (new AuthCode())->init(),
        ];
        return $this;
    }

    /**
     * Получить цифровой код статуса сообщения
     * @param string $event
     * @return string
     */
    public function getStatusCode(string $event): string
    {
        return '0';
    }

    /**
     * Получить новое сообщение или обновить статус полученного ранее по веб-хуку
     * @return Message|null
     */
    public function get(): ?Message
    {
        return new Message();
    }

    /**
     * Отправить текстовое сообщение в мессенджер
     * @param Message $message
     * @return Message|null
     */
    public function sendText(Message $message): ?Message
    {
        return new Message();
    }
}