<?php

namespace Messengers\Telegram;

use JsonException;
use Messengers\Config as MainConfig;
use Messengers\Core\Dialogs\Dialogs;
use Messengers\Core\Models\Message;
use Messengers\ProviderInterface;
use stdClass;

final class Telegram implements ProviderInterface
{

    use GetMessagesTrait;

    /**
     * Директория для сохранения медиа файлов мессенджера
     */
    private const FILES_DIR = MainConfig::FILES_DIR . '/telegram';

    private static function updateStatus(int $sender_id): void
    {
        $messages = (new Message())->all(['sender_id' => $sender_id, 'status' => 0]);
        foreach ($messages as $message) {
            $message->status = '2';
            $message->save();
        }
    }

    /**
     * Получить цифровой код статуса сообщения
     * (На данный момент Телеграм не поддерживает данный функционал)
     * @param string $event
     * @return string
     */
    public function getStatusCode(string $event): string
    {
        return '0';
    }

    private static $messageTypes = [
        'document',
        'photo',
        'text',
        'video',
        'audio',
        'sticker',
        'animation'
    ];

    /**
     * Получить новое сообщение или обновить статус полученного ранее по веб-хуку
     * @return Message|null
     * @throws JsonException
     */
    public function get(): ?Message
    {
        $hook = json_decode(file_get_contents('php://input'), false, 512, JSON_THROW_ON_ERROR);
        $message = new Message();
        $message->chat_id = $hook->message->chat->id;
        $message->sender_id = $hook->message->from->id;
        $message->messenger_type = 'telegram';
        $message->message_id = $hook->message->message_id;
        $message->is_client = 1;
        $message->manager_id = Message::getManagerId($message);
        $saveMethod = $this->getSaveMethod($hook);
        if($saveMethod) {
            $saveMessage = $this->$saveMethod($message, $hook);
            Dialogs::init($saveMessage);
            self::updateStatus($hook->message->from->id);
            return $saveMessage;
        }
        return null;
    }

    /**
     * Отправить текстовое сообщение в мессенджер
     * @param Message $message
     * @return Message|null
     * @throws JsonException
     */
    public function sendText(Message $message): ?Message
    {
        $managerId = $message->manager_id;
        if (!$managerId) {
            $managerId = Message::getManagerId($message);
        }
        $url = Config::MAIN_URL . '/sendMessage';
        $body = (object)[
            'chat_id' => $message->chat_id,
            'text' => $message->body,
            'protect_content' => true
        ];
        $hook = $this->request($url, $body);
        if ($hook) {
            $message->message_id = $hook->result->message_id;
            $message->manager_id = $managerId;
        }
        return $message;
    }

    /**
     * Инициализация мессенджера (установка веб-хуков и прочее)
     * @return stdClass
     * @throws JsonException
     */
    public function init(): stdClass
    {
        $body = (object)[
            'url' => MainConfig::BASE_URL . '/telegram/get',
            'allowed_updates' => [],
            'drop_pending_updates' => true
        ];
        $this->request(Config::SET_WEBHOOK_URL, $body);
        return $this->request(Config::MAIN_URL . '/getWebhookInfo');
    }

    public function info()
    {
        $url = Config::MAIN_URL . '/getMe';
        return $this->request($url);
    }

    /**
     * @throws JsonException
     */
    private function request(string $url, ?object $body = null, string $method = 'post'): ?stdClass
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => mb_strtoupper($method),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];
        if ($body && mb_strtoupper($method) !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_THROW_ON_ERROR);
        }
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, false, 512, JSON_THROW_ON_ERROR) ?: null;
    }

    private function getSaveMethod(stdClass $hook): ?string
    {
        foreach (self::$messageTypes as $method) {
            if (isset($hook->message->$method)) {
                return 'save' . ucfirst($method);
            }
        }
        return null;
    }


    public function get_all():array
    {
        return (new Message())->all(['messenger_type'=>'telegram']);
    }

}