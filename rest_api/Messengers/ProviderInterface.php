<?php

namespace Messengers;

use JsonException;
use Messengers\Core\Models\Message;

interface ProviderInterface
{

    /**
     * Получить цифровой код статуса сообщения
     * @param string $event
     * @return string
     */
    public function getStatusCode(string $event): string;

    /**
     * Получить новое сообщение или обновить статус полученного ранее по веб-хуку
     * @return Message|null
     */
    public function get(): ?Message;

    /**
     * Отправить текстовое сообщение в мессенджер
     * @param Message $message
     * @return Message|null
     * @throws JsonException
     */
    public function sendText(Message $message): ?Message;

    /**
     * Инициализация мессенджера (установка веб-хуков и прочее)
     */
    public function init();
}