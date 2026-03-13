<?php

require_once 'Simpla.php';
require_once '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Класс для работы с микросервисом Notification Center
 * gitlab: https://gcib0d3c8bstra.boostra.ru/collection/notification-center
 */
class NotificationCenter extends Simpla
{
    const API_URL = 'https://nc.mkkcollection.ru/api/';
    const API_URL_TEST = 'https://nc.dev.mkkcollection.ru/api/';

    const ERROR_LOG_FILE = 'notification-center-error.txt';

    private Client $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => $this->config->is_dev ? self::API_URL_TEST : self::API_URL,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }
    public function signAdditionalCommunicationDoc(string $zaim_number): array
    {
        $url = 'contract/'.$zaim_number.'/sign_additional_communication_doc/';
        $params = [];
        try {
            $response = $this->client->get($url);

            if ($response->getStatusCode() !== 200) {
                $this->logging(__METHOD__, $url.'Init', $params, json_decode($response->getBody()->getContents(), true), date('d-m-Y').self::ERROR_LOG_FILE);

                return [];
            }

            $response = json_decode($response->getBody()->getContents(), true);
            $this->logging(__METHOD__, $url.'Init', $params, $response, date('d-m-Y').self::ERROR_LOG_FILE);
            return $response;
        } catch (RequestException $e) {
            $this->logging(__METHOD__, $url.'Init', $params, json_decode($e->getResponse()->getBody()->getContents(), true), date('d-m-Y').self::ERROR_LOG_FILE);

            return [];
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, $url.'Init', $params, ['error' => $e->getMessage()], date('d-m-Y').self::ERROR_LOG_FILE);

            return [];
        }

    }
}