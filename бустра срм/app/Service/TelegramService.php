<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TelegramService
{
    private string $token;
    private string $apiUrl = 'https://api.telegram.org/bot';
    private Client $client;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->apiUrl .= $this->token;
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
    }

    /**
     * Отправка текстового сообщения
     * @param string|int $chatId
     * @param string $text
     * @param array $options Дополнительные параметры
     * @return bool
     */
    public function sendMessage($chatId, string $text, array $options = []): bool
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text
        ], $options);

        return $this->sendRequest('sendMessage', $params);
    }

    /**
     * Отправка аудио файла
     * @param string|int $chatId
     * @param string $audio URL или путь к аудио файлу
     * @param array $options Дополнительные параметры:
     *  - caption: string|null Подпись к аудио
     *  - duration: int|null Продолжительность в секундах
     *  - performer: string|null Исполнитель
     *  - title: string|null Название
     *  - message_thread_id: int|null ID темы для форумов
     *  - parse_mode: string|null Режим форматирования для подписи
     * @return bool
     */
    public function sendAudio($chatId, string $audio, array $options = []): bool
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'audio' => $audio
        ], $options);

        return $this->sendRequest('sendAudio', $params);
    }

    /**
     * Отправка запроса к API Telegram
     * @param string $method
     * @param array $params
     * @return bool
     */
    private function sendRequest(string $method, array $params): bool
    {
        try {
            $response = $this->client->post($this->apiUrl . '/' . $method, [
                'form_params' => $params
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['ok'] ?? false;

        } catch (Exception | GuzzleException $e) {
            error_log("Telegram API Error: " . $e->getMessage());
            return false;
        }
    }
}
