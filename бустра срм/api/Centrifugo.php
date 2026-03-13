<?php

require_once 'Simpla.php';

/**
 * Класс для работы с web сокетом Centrifugo
 */
class Centrifugo extends Simpla
{
    private \GuzzleHttp\Client $guzzleClient;

    public function __construct()
    {
        parent::__construct();

        $this->guzzleClient = new GuzzleHttp\Client(
            [
                'base_uri' => $this->config->CENTRIFUGO['http_url'] . '/api/',
                'headers' => [
                    'Authorization' => 'apikey ' . $this->config->CENTRIFUGO['http_api_key'],
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    /**
     * Отправка данных в канал
     * @param string $channel
     * имя канала например связка order_update.user_id
     * @param array $data
     * любые данные массивом
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function publishToChannel(string $channel, array $data = []): string
    {
        $result = $this->guzzleClient->post('publish', [
            'json' => compact('channel', 'data'),
        ]);

        return $result->getBody()->getContents();
    }
}
