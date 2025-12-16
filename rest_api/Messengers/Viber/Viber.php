<?php

namespace Messengers\Viber;

use JsonException;
use Messengers\Config as MainConfig;
use Messengers\Core\Dialogs\Dialogs;
use Messengers\Core\Models\Manager;
use Messengers\Core\Models\Message;
use Messengers\Main;
use Messengers\ProviderInterface;
use Messengers\Response;
use stdClass;

/**
 *
 */
final class Viber implements ProviderInterface
{

    use GetEvenTrait, GetMessageTrait;

    /**
     * Директория для сохранения медиа файлов мессенджера
     */
    private const FILES_DIR = MainConfig::FILES_DIR . '/viber';

    /**
     * Заголовки для отправки
     * @var array
     */
    private static $headers = [
        'X-Viber-Auth-Token:' . Config::BOT_TOKEN,
        'Content-Type:application/json'
    ];

    /**
     * Установить веб-хук для получения новых сообщений
     * @throws JsonException
     */
    public function init()
    {
        $body = (object)[
            'url' => MainConfig::BASE_URL . '/viber/get',
            'event_types' => Config::EVENT_TYPES,
            'send_name' => true,
            'send_photo' => true
        ];
        return $this->request(Config::SET_WEBHOOK_URL, $body);
    }

    /**
     * Отправить запрос на сервер viber
     * @param string $url
     * @param object $body
     * @return stdClass
     * @throws JsonException
     */
    private function request(string $url, object $body): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => 'utf-8',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body, JSON_THROW_ON_ERROR),
                CURLOPT_HTTPHEADER => self::$headers,
            ]
        );
        $response = json_decode(curl_exec($curl), false, 512, JSON_THROW_ON_ERROR);
        curl_close($curl);
        return $response;
    }

    /**
     * Получить новое сообщение или обновить статус полученного ранее
     * @return void
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
        if ($hook && $hook->event === 'message') {
            $messageType = 'saveMessage' . ucfirst($hook->message->type);
            if (method_exists($this, $messageType) && $message = $this->$messageType($hook)) {
                $message->is_client = 1;
                Dialogs::init($message);
                return $message;
            }
        } elseif ($hook && $hook->event !== 'message') {
            $messageType = 'event' . ucfirst($hook->event);
            if (method_exists($this, $messageType) && $message = $this->$messageType($hook)) {
                return $message;
            }
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
        $manager = (new Manager())->getManagerInfo($managerId);
        $body = (object)[
            'receiver' => $message->sender_id,
            'min_api_version' => 1,
            'sender' => (object)[
                'name' => $manager->name,
                'avatar' => $manager->avatar
            ],
            'tracking_data' => 'tracking data',
            'type' => 'text',
            'text' => $message->body
        ];
        $request = $this->request('https://chatapi.viber.com/pa/send_message', $body);
        $message->message_id = $request->message_token;
        $message->status = $this->getStatusCode($request->billing_status);
        $message->manager_id = $managerId;
        $message->chat_id = $request->chat_hostname;
        return $message;
    }

    /**
     * Получить цифровой код статуса сообщения
     * @param string|null $event
     * @return string
     */
    public function getStatusCode(string $event = null): string
    {
        $data = [
            'send' => 0, // отправлено
            'delivered' => '1', // доставлено
            'seen' => '2', // просмотрено
        ];
        foreach ($data as $key => $value) {
            if ($key === $event || $value === $event) {
                return $value;
            }
        }
        return '3'; // Не доставлено
    }

    /**
     * Получить тело сообщения в виде текста HTML для медиа сообщений
     * @param stdClass $hook
     * @return string
     */
    private function getMediaBody(stdClass $hook): ?string
    {
        $ext = $this->getExtMedia($hook);
        $fileDir = self::FILES_DIR . '/' . $hook->message->type;
        $file = $fileDir . '/' . time() . random_int(0, 99999) . '.' . $ext;
        if (Main::createFilesDir($fileDir)) {
            file_put_contents($file, file_get_contents($hook->message->media));
        }
        if (is_file($file)) {
            return $this->getHtmlToType(str_replace(ROOT_DIR, MainConfig::MAIN_URL, $file), $hook);
        }
        return null;
    }

    private function getExtMedia(stdClass $hook): string
    {
        return preg_replace('~(.+)\.(\w+)(.+)?~u', '$2', $hook->message->media);
    }

    private function getHtmlToType(string $file, stdClass $hook): ?string
    {
        return $this->{$hook->message->type}($hook, $file);
    }

    /**
     * @param Message $message
     * @return Message|null
     * @throws JsonException
     */
    private function return(Message $message): ?Message
    {
        if ($message->body && $res = $message->save()) {
            return $res;
        }
        return null;
    }

    public function get_all():array
    {
        return (new Message())->all(['messenger_type'=>'viber']);
    }

    public function info()
    {
        $data = (object) [];
        $url = 'https://chatapi.viber.com/pa/get_account_info';
        return $this->request($url, $data);
    }
}