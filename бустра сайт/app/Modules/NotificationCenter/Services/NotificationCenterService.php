<?php

namespace App\Modules\NotificationCenter\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class NotificationCenterService
{
    private Client $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
    }

    public function post(array $data, string $url): array
    {
        try {
            $response = $this->guzzle->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.notification_center.api_token'),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data
            ]);

            $result = $response->getBody()->getContents();

            logger('nc')->info(__METHOD__ . PHP_EOL . PHP_EOL
                . 'url' . PHP_EOL . $url . PHP_EOL . PHP_EOL
                . 'request data: ' . PHP_EOL . json_encode($data, 256) . PHP_EOL . PHP_EOL
                . 'response data: ' . PHP_EOL . $result
            );

            return json_decode($result, true);
        } catch (Exception | GuzzleException $e) {
            logger('nc')->error(
                __METHOD__ . PHP_EOL
                . 'URL: ' . $url . PHP_EOL
                . $e->getFile() . PHP_EOL
                . $e->getLine() . PHP_EOL
                . $e->getMessage());

            return [];
        }
    }

    public function getUrl(): string
    {
        $url = isProduction()
            ? config('services.notification_center.url_prod')
            : config('services.notification_center.url_dev');

        if (empty($url)) {
            logger('nc')->error("Не получилось получить урл");
            return '';
        }

        return $url;
    }
}