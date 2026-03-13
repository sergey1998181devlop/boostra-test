<?php

namespace Messengers\Viber;

use JsonException;
use Messengers\Core\Models\Message;
use stdClass;

trait GetMessageTrait
{

    use GetHtmlToTypeMessageTrait;

    /**
     * Получить текстовое сообщение
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageText(stdClass $hook): ?Message
    {
        $message = new Message();
        $message->status = $this->getStatusCode('send');
        $message->sender_id = $hook->sender->id;
        $message->body = $this->text($hook);
        $message->message_id = $hook->message_token;
        $message->messenger_type = 'viber';
        $message->is_client = 1;
        $message->manager_id = Message::getManagerId($message);
        return $this->return($message);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessagePicture(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMediaMessage(stdClass $hook): ?Message
    {
        $message = new Message();
        $message->status = $this->getStatusCode('failed');
        $message->sender_id = $hook->sender->id;
        $message->body = $this->getMediaBody($hook);
        $message->message_id = $hook->message_token;
        $message->chat_id = $hook->chat_hostname;
        $message->messenger_type = 'viber';
        $message->manager_id = Message::getManagerId($message);
        $message->is_client = 1;
        return $this->return($message);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageVideo(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageFile(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageContent(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageUrl(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageContact(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageSticker(stdClass $hook): ?Message
    {

        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageCarousel(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }

    /**
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function saveMessageLocation(stdClass $hook): ?Message
    {
        return $this->saveMediaMessage($hook);
    }
}