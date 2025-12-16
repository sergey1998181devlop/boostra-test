<?php

namespace Messengers\WhatsApp;

use JsonException;
use Messengers\Config as MainConfig;
use Messengers\Core\Dialogs\Dialogs;
use Messengers\Core\Models\Message;
use Messengers\ProviderInterface;
use stdClass;

final class WhatsApp implements ProviderInterface
{
    use SaveMessagesTrait;

    /**
     * Директория для сохранения медиа файлов мессенджера
     */
    private const FILES_DIR = MainConfig::FILES_DIR . '/whatsapp';

    /**
     * @var array
     */
    private $headers;

    public function __construct()
    {
        $this->headers = [
            'Authorization: ' . Config::TOKEN
        ];
    }

    /**
     * Получить цифровой код статуса сообщения
     * @param string $event
     * @return string
     */
    public function getStatusCode(string $event): string
    {
        $data = [
            'pending' => 0, // отправлено
            'delivered' => '1', // доставлено
            'read' => '2', // просмотрено
        ];
        foreach ($data as $key => $value) {
            if ($key === $event || $value === $event) {
                return $value;
            }
        }
        return '3'; // Не доставлено
    }

    /**
     * Получить новое сообщение или обновить статус полученного ранее по веб-хуку
     * @return Message|null
     * @throws JsonException
     */
    public function get(): ?Message
    {
        $hook = json_decode(
            file_get_contents('php://input'),
            false,
            512,
            JSON_THROW_ON_ERROR
        );
        if (is_array($hook->messages)) {
            foreach ($hook->messages as $message) {
                $newMessage = new Message();
                $newMessage->message_id = $message->id;
                $newMessage->sender_id = $message->from;
                $newMessage->is_client = 1;
                $newMessage->messenger_type = 'whatsapp';
                $newMessage->manager_id = Message::getManagerId($newMessage);
                $saveMethod = $this->getSaveMessageType($message);
                if (method_exists($this, $saveMethod)) {
                    $addMessage = $this->$saveMethod($newMessage, $message);
                    Dialogs::init($addMessage);
                    return $addMessage;
                }
            }
        }
        if (isset($hook->messages->wh_type) && $hook->messages->wh_type = 'delivery_status') {
            return $this->updateStatusMessage($hook->messages);
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
        $message->manager_id = $managerId;
        $body = (object)[
            'recipient' => $message->sender_id,
            'body' => $message->body
        ];
        $url = Config::MAIN_URL . '/sync/message/send';
        $url .= '?' . http_build_query(
                [
                    'profile_id' => Config::PROFILE_ID,
                ]
            );
        $request = $this->request($url, $body);
        if (isset($request->status) && $request->status !== 'error') {
            $message->message_id = $request->message_id;
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
        $url = Config::MAIN_URL . '/webhook/url/set';
        $url .= '?' . http_build_query(
                [
                    'profile_id' => Config::PROFILE_ID,
                    'url' => MainConfig::BASE_URL . '/whatsapp/get'
                ]
            );
        $whatsapp['webhook'] = $this->request($url);
        $url = Config::MAIN_URL . '/webhook/types/set';
        $url .= '?' . http_build_query(
                [
                    'profile_id' => Config::PROFILE_ID,
                ]
            );
        $whatsapp['types'] = $this->request($url, Config::WEBHOOK_TYPES);
        return (object)$whatsapp;
    }


    /**
     * @param string $url
     * @param array|object $body
     * @param string|null $method
     * @return stdClass
     * @throws JsonException
     */
    private function request(string $url, $body = null, string $method = 'post'): stdClass
    {
        $curl = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => mb_strtoupper($method),
            CURLOPT_HTTPHEADER => $this->headers,
        ];
        if ($body) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_THROW_ON_ERROR);
        }
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Обновить статус сообщения
     * @param stdClass $hookMessage
     * @return Message|null
     * @throws JsonException
     */
    private function updateStatusMessage(stdClass $hookMessage): ?Message
    {
        $message = (new Message())->find(['message_id' => $hookMessage->id]);
        if ($message) {
            $message->status = $this->getStatusCode($hookMessage->status);
            return $message->save();
        }
        return null;
    }

    private static $saveMethods = [
        'chat' => 'text',
        'image' => 'picture',
        'video'=>'video',
        'document'=>'document',
        'file'=>'file',
        'audio'=>'audio',
        'ptt' => 'audio',
    ];

    private function getSaveMessageType(stdClass $message): ?string
    {
        foreach (self::$saveMethods as $messageType => $saveMethod) {
            if (mb_strtolower($messageType) === mb_strtolower($message->type)) {
                return $saveMethod;
            }
        }
        return null;
    }
}