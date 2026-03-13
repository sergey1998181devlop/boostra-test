<?php

namespace App\Modules\RecurrentsCenter\Services;

use App\Modules\SbpAccount\Services\SbpAccountService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RecurrentCenterService
{
    private Client $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
    }

    public function sendRequest(array $data, string $url): void
    {
        try {
            $response = $this->guzzle->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.recurrent_center.api_token'),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data
            ]);

            $result = $response->getBody()->getContents();

            logger('rc')->info(__METHOD__ . PHP_EOL . PHP_EOL
                . 'url' . PHP_EOL . $url . PHP_EOL . PHP_EOL
                . 'request data: ' . PHP_EOL . json_encode($data, 256) . PHP_EOL . PHP_EOL
                . 'response data: ' . PHP_EOL . $result
            );
        } catch (Exception | GuzzleException $e) {
            logger('rc')->error(
                __METHOD__ . PHP_EOL
                . 'URL: ' . $url . PHP_EOL
                . $e->getFile() . PHP_EOL
                . $e->getLine() . PHP_EOL
                . $e->getMessage());
        }
    }

    public function getUrl(): string
    {
        $url = isProduction()
            ? config('services.recurrent_center.url_prod')
            : config('services.recurrent_center.url_dev');

        if (empty($url)) {
            logger('rc')->error("Не получилось получить урл");
            return '';
        }

        return $url;
    }

    /**
     * Деактивировать (удалить) СБП-токен клиента в RC/MKK Collection
     */
    public function deleteSbpToken(string $token, string $clientUid, int $deleted = 1): void
    {
        $url = $this->getUrl() . SbpAccountService::DELETE_API_URL;
        if (empty($url)) {
            logger('rc')->error('RC url is empty in ' . __METHOD__);
            return;
        }

        $this->sendRequest([
            'token' => $token,
            'deleted' => $deleted,
            'client_uid' => $clientUid,
        ], $url);
    }
}