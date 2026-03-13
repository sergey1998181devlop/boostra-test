<?php

declare(strict_types=1);

namespace services\Integration\BankiRu\Client;

use services\Integration\BankiRu\BankiRu;

class Client implements ClientInterface
{
    public const GET = 'get';
    public const POST = 'post';

    private $baseUrl;
    public function __construct(bool $isProduction = false)
    {
        $banki = BankiRu::getInstance();
        $config = $banki->getConfig();

        if ($isProduction === false) {
            $isProduction = (bool) $config['is_production'];
        }

        $this->baseUrl = $isProduction ? $config['production_url'] : $config['dev_url'];
    }

    public function request(string $method, string $url, array $params = [], array $headers = []): array
    {
        if (isset($_GET['is_debug_curl'])) {
            echo "<pre>";
            var_dump($this->baseUrl . $url, $params, $headers);
            die;
        }

        $curlHandle = curl_init($this->baseUrl . $url);
        if (!empty($headers)) {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method === self::POST) {
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($curlHandle, CURLOPT_POST, true);
        }
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curlHandle);
        curl_close($curlHandle);

        if (isset($_GET['is_debug_response'])) {
            echo "<pre>";
            var_dump($response);
            die;
        }

        if ($response === false) {
            throw new \Exception(curl_error($curlHandle));
        }

        return (array) json_decode($response, true);
    }
}