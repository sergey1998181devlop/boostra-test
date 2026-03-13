<?php

require_once __DIR__ . '/interfaces/NotifierInterface.php';
require_once __DIR__ . '/Simpla.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Telegram
 * Класс для работы с API TG
 */
class Telegram extends Simpla implements NotifierInterface
{
    private ?string $token;
    private ?string $chat_id;
    private array $additionalParams = [];
    private Client $client;

    /**
     * Данные для баланса смс
     */
    public const BOOSTRA_SMSC_BALANCE = [
        'token' => '5517708518:AAGFoE7bdWOps3iJt4IUp0-9xobpwWMgFW0',
        'chat_id' => '-1001698887903',
    ];

    /**
     * Данные о состоянии денежного баланса Boostra
     */
    public const BOOSTRA_1C_MONEY_BALANCE = [
        'token' => '6054396090:AAFcEsg-0DAtNSlSzFy4CkT-iH9jNXdoW0g',
        'chat_id' => '-1001894997640',
    ];

    public function __construct($token = null, $chat_id = null, $additionalParams = [])
    {
        parent::__construct();
        $this->token = $token ?? config('services.telegram.token');
        $this->chat_id = $chat_id ?? config('services.telegram.notifications_chat_id');
        $this->additionalParams = $additionalParams;
        $this->client = new Client([
            'base_uri' => 'https://api.telegram.org/bot' . $this->token . '/',
            'timeout' => 10,
            'verify' => false
        ]);
    }

    private function callApi(string $method, array $params)
    {
        if (empty($this->token)) {
            return null;
        }

        try {
            $response = $this->client->post($method, [
                'form_params' => $params
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (Exception | GuzzleException $e) {
            $this->logging(
                'Telegram::callApi', 
                $method, 
                $params, 
                $e->getMessage(), 
                'telegram_errors.txt'
            );
            return null;
        }
    }

    public function sendMessage($message, array $params = [])
    {
        if (empty($this->token) || empty($this->chat_id)) {
            return null;
        }

        $params = array_replace([
            'chat_id' => $this->chat_id,
            'text'    => $message,
            'parse_mode' => 'HTML',
        ], $this->additionalParams, $params);

        return $this->callApi('sendMessage', $params);
    }

    public function sendAudio(string $audio, array $options = [])
    {
        if (empty($this->token) || empty($this->chat_id)) {
            return null;
        }

        $params = array_merge([
            'chat_id' => $this->chat_id,
            'audio' => $audio
        ], $options);

        return $this->callApi('sendAudio', $params);
    }
}
